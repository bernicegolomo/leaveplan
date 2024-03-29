<?php
/*************************************************************************/
/*               (c) ACTIVE SPACE TECHNOLOGIES  2010                     */
/*************************************************************************/
 
/* LEAVE PLAN for OrangeHRM 
 *
 * This script allows to have a public LEAVE PLAN for all your employees
 * using the OrangeHRM software. Tested with OrangeHRM 2.5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 *
 * @category absences
 * @package OrangeHRM
 * @author Andr� Tenreiro (andre.tenreiro@activespacetech.com)
 * @copyright (c) 2010 Active Space Technologies
 * @license http://www.gnu.org/licenses/gpl-3.0.txt (GPL 3.0)
 * @version 1.0
 * @link http://www.activespacetech.com
 * @since Class available since Release 1.0
 */


/*****************/
/* Configuration */
/*****************/

//Database information
$db_host = "localhost";
$db_user = "user";
$db_pwd = "password";
$db_name = "orangeHRM";
$db_port = 3306;

//Logo (optional) - Set empty has an empty string for none  (examples: "logo.png", "img/logo.png", "http://my.url.com/logo.png")
$logoLink = "";




/******************************/
/* DONT EDIT BELLOW THIS LINE */
/******************************/














//Year
if (isset($_GET['year']))
	$year = $_GET['year'];
else
	$year = date("Y");

//Month
if (isset($_GET['month']))
	$month = $_GET['month'];
else
	$month = date("m");

$date_aux = $year . "-" . $month;
$seeDate = date("Y-m", strtotime($date_aux));	
	
/* Number of days in a month/Year */	
function monthDays($month, $year) {
	return date("t", strtotime($year . "-" . $month . "-01"));
}//

	
//admin get Years
function getYears($year) {

global $db_host, $db_user, $db_pwd, $db_name, $db_port;
	
	if(!isset($year))
		$year = date("Y");
	
	$sql = "SELECT DISTINCT(EXTRACT(Year FROM leave_date)) FROM hs_hr_leave WHERE 1 ORDER BY leave_date;";
	
	// Make a MySQL Connection
	mysql_connect($db_host, $db_user, $db_pwd) or die(mysql_error());
	mysql_select_db($db_name) or die(mysql_error());
	
	//Query
	$result = mysql_query($sql) or die(mysql_error());  
	
	
	while ($row = mysql_fetch_array($result )) {
	
		if ($row['(EXTRACT(Year FROM Date))'] == $year) {
			echo "<option value=\"".$row['(EXTRACT(Year FROM leave_date))']."\" selected>".$row['(EXTRACT(Year FROM leave_date))']."</option><br>";
		} else {
			echo "<option value=\"".$row['(EXTRACT(Year FROM leave_date))']."\">".$row['(EXTRACT(Year FROM leave_date))']."</option><br>";
		}
	
	}
		
}//	

	
function weekDay($date) {

$thisDay = date("D", strtotime($date));
return $thisDay;

}//
	
function checkDay($employee_id, $i, $month, $year) {

global $db_host, $db_user, $db_pwd, $db_name, $db_port;
		
		$date = $year . "-" . $month . "-" . $i;

		
		$sql = "SELECT * FROM hs_hr_holidays WHERE date = '$date';";
		
		mysql_connect($db_host, $db_user, $db_pwd) or die(mysql_error());
		mysql_select_db($db_name) or die(mysql_error());
	
		$result = mysql_query($sql)or die(msysql_error());  
		$result_count = mysql_num_rows($result);
	
		if ($result_count > 0)
			return "holiday";
		
				
		if( ( weekDay($date) == "Sat") || ( weekDay($date) == "Sun") ) {			
			return "weekend";
		}			
		
		
		//Get Absences
		$sql = "SELECT leaves.* 
		FROM `hs_hr_leave` AS leaves
		WHERE (leaves.employee_id = '$employee_id') AND (leaves.leave_date = '$date') AND (leaves.leave_status > 1)
		ORDER BY leaves.leave_date ASC;";	

		mysql_connect($db_host, $db_user, $db_pwd) or die(mysql_error());
		mysql_select_db($db_name) or die(mysql_error());
	
		$result = mysql_query($sql)or die(mysql_error());  
		$result_count = mysql_num_rows($result);
		
		if ( $result_count == 0 )  {
			return "default";
		} else {
			while ($row = mysql_fetch_array( $result ) ) {
				if ($row['leave_length_days'] < 1.0 ) {
					if ( ($row['start_time'] >= "08:00:00") && ($row['start_time'] <= "09:00:00")  )
						return "absence_partial_m";
					else if ( ($row['start_time'] >= "13:00:00") && ($row['start_time'] <= "18:00:00")  )
						return "absence_partial_a";
				} else
					return "absence_full";					
					
			
			}//
		}
}	
	

$numDays = monthDays($month, $year);
?>

<HTML>
<HEAD><TITLE>Vacation Plan</TITLE>
<script type="text/javascript">
function sort(form) {
	
	
		var Page = "?";
		
		var month = form.listMonth.selectedIndex+1;		
		var iyear = form.listYear.selectedIndex;
		var year = form.listYear.options[iyear].value;	
		
		var URL = Page + "month=" + month + "&year=" + year;
		
		window.location = URL;	
		
		return false;
	}
</script>
<style type="text/css">
<!--
.style2 {font-size: 12px; }
-->
</style>
</HEAD>
<BODY>

<?php if (!empty($logoLink))	
	echo "<img src='$logoLink'/>";
?>
<center>
  <div id="sort" style="padding-right: 10%">
    <form id="frmSort" name="frmSort" onsubmit="return sort(this); return false;">
      <? 
$seeDate = date("F Y", strtotime($seeDate));
echo "<h2>".$seeDate."</h2>";
?>
      <div align="right">
      <strong>Month</strong>:
      <?php
			$curr_month = $month;
				
			$auxmonth = array (1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
			$select = "<select name=\"listMonth\">\n";
		
			foreach ($auxmonth as $key => $val) {
				$select .= "\t<option val=\"".$key."\"";
			if ($key == $curr_month) {
				$select .= " selected>".$val."\n";
			} else {
				$select .= ">".$val."\n";
			}
		}
		echo $select;
	?>
      </select>
      <img src="img/pixel.gif" width="1" height="1" /><img src="img/pixel.gif" width="1" height="1" /> <img src="img/pixel.gif" width="1" height="1" /><img src="img/pixel.gif" width="1" height="1" /> <img src="img/pixel.gif" width="1" height="1" /><strong>Year</strong>:
      <select name="listYear" id="listYear">
        <?php getYears($year); ?>
      </select>
      <img src="img/pixel.gif" width="1" height="1" /> <img src="img/pixel.gif" width="1" height="1" />
      <input type="submit" name="Submit" value="Select" />
    </form>
</div>
<div id="table">
<table width="90%" border="0" cellspacing="1" cellpadding="1">
	<tr>
		<td >&nbsp;</td>
		<? 
		for($i = 1; $i <= $numDays; $i++) {
			 echo "<td bgcolor=\"#FFFFFF\" width=\"15\"><div align=\"center\"><strong>".$i."</strong></div></td>";
			}
		?>
	</tr>
	
		<?
global $db_host, $db_user, $db_pwd, $db_name, $db_port;
		
		$sql = "SELECT emp.*
			    FROM hs_hr_employee AS emp
			    INNER JOIN hs_hr_users AS users ON emp.emp_number = users.emp_number
			    WHERE (users.status = 'Enabled')
			    ORDER BY emp.emp_firstname ASC";
		
		mysql_connect($db_host, $db_user, $db_pwd) or die(mysql_error());
		mysql_select_db($db_name) or die(mysql_error());
	
		$result = mysql_query($sql)or die(mysql_error());  
		$result_count = mysql_num_rows($result);
	
		$even = 1;
		
		while ($row = mysql_fetch_array( $result ) ) {
			$employee_id = $row['employee_id'];
			$employee_num = $row['emp_number'];
			$employee_firstname = $row['emp_firstname'];
			$employee_lastname = $row['emp_lastname'];
			
			echo "<tr>";
			
			if ( $even % 2) 
				echo " <td bgcolor=\"#FFFFFF\" width=\"10%\"><div align=\"right\">".$employee_firstname . " " . $employee_lastname . "</div></td>";
			else
				echo " <td bgcolor=\"#FFFFFF\" width=\"10%\"><div align=\"right\">".$employee_firstname . " " . $employee_lastname . "</div></td>";
			
			$even++;
			
			for($i = 1; $i <= $numDays; $i++) {
			
				$day = checkDay($employee_num, $i, $month, $year);
				
				switch($day) {				
					case 'holiday':
						echo " <td bgcolor=\"#0000FF\"></td>";
						break;
					case 'weekend':
						echo " <td bgcolor=\"#999999\"></td>";
						break;
					case 'absence_full':
						echo " <td bgcolor=\"#FF0000\"></td>";
						break;
					case 'absence_partial_m':
						echo " <td bgcolor=\"#FFFF00\"><center>M</center></td>";
						break;
					case 'absence_partial_a':
						echo " <td bgcolor=\"#FFFF00\"><center>A</center></td>";
						break;
					default:
						echo " <td bgcolor=\"##00FF00\"></td>";
						break;				
				}//switch
			}
			
			echo "</tr>";
		}//while
		
		?>
	
</table>
</div>	
</center>	
<br/>
<div style="padding-left: 5%">
<table width="25%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td bgcolor="#00FF00">&nbsp;</td>
    <td><div align="left" class="style2">Available</div></td>
  </tr>
  <tr>
    <td width="11%" bgcolor="#FF0000">&nbsp;</td>
    <td width="89%"><div align="left" class="style2">Absence</div></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC">&nbsp;</td>
    <td><div align="left" class="style2">WeekEnd</div></td>
  </tr>
  <tr>
    <td bgcolor="#0000FF">&nbsp;</td>
    <td><div align="left" class="style2">Public Holiday </div></td>
  </tr>
  <tr>
    <td bgcolor="#FFFF00"><div align="center">M</div></td>
    <td><div align="left" class="style2">Partial Absence (Morning) </div></td>
  </tr>
  <tr>
    <td bgcolor="#FFFF00"><div align="center">A</div></td>
    <td><span class="style2">Partial Absence (Afternoon) </span></td>
  </tr>
</table>
</div>
<br/>
<div align="center">� 2010 - <a href="http://code.google.com/p/leaveplan">Leave Plan Plugin</a> for OrangeHRM - Under GPL</div>
</BODY>
</HTML>