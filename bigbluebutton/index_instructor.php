<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH . 'vitals.inc.php');
authenticate(AT_PRIV_BIGBLUEBUTTON);
$_custom_css = $_base_path . 'mods/bigbluebutton/module.css'; // use a custom stylesheet
require (AT_INCLUDE_PATH.'header.inc.php');
require "bbb_api_conf.php";
require "bbb_api.php";

echo"<img src='/atutor/docs/mods/bigbluebutton/bigbluebutton.png'>
	<br>
	<br>
	<div id='bigbluebutton'>";

 
 $bbb_joinURL;
 $courseId           = $_SESSION['course_id'];
 $courseTiming       = $_GET['course_timing'];
 $courseMessage      = $_GET['course_message'];
 $moderatorPassword  = "mp";
 $attendeePassword   = "ap";   
 $logoutUrl          = "http://bigbluebutton.org";
 $username           = get_login(intval($_SESSION['member_id']));
 $meetingID          = $_SESSION['course_id'];
 
 
 $response           = BigBlueButton::createMeetingArray($username,
                                                         $meetingID,
                                                         "welcome to the Classroom",
                                                         $moderatorPassword,
                                                         $attendeePassword,
                                                         $salt,
                                                         $url,
                                                         $logoutUrl);

	//Analyzes the bigbluebutton server's response
	if (!$response){//If the server is unreachable
	    $msg = 'Unable to join the meeting. Please check the url of the bigbluebutton server AND check to see if the bigbluebutton server is running.';
	}
	else if ( $response['returncode'] == 'FAILED' ) { //The meeting was not created
	    if ($response['messageKey'] == 'checksumError'){
		    $msg = 'A checksum error occured. Make sure you entered the correct salt.';
		}
		else {
		    $msg = $response['message'];
		}
	}
	else { //The meeting was created, and the user will now be joined
	    $bbb_joinURL = BigBlueButton::joinURL($meetingID,
	                                          $username,
	                                          $moderatorPassword,
	                                          $salt,
	                                          $url);
		
	}

//-----------------------------------------------

	
	if($_GET['submit_button']=='submit')
    {
	
      require(AT_INCLUDE_PATH . 'classes/sqlutility.class.php');
      $sql = "INSERT INTO ".TABLE_PREFIX."bigbluebutton VALUES ('$_courseId','$_courseTiming','$_courseMessage')";
	  $result = mysql_query($sql, $db);
    }
    
    
//---------------------------------------------------
if ($_GET['Edit_button']=="Edit")
{
	echo "<b>Edit</b></br>";
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="form">
<?php 
    
    $_courseTiming   = $_GET['course_timing'];
    $_courseMessage  = $_GET['course_message'];
    echo "<table border='2'>
              <tr>
                  <td>Course timing</td>
                  <td>
                      <input type='hidden' name='create_classroom' value='checked'>
                      <input type='text' name='course_timing'  value='$_courseTiming'/>
                  </td>                
              </tr>
              <tr>
                  <td>
                      Message
                  </td>
                  <td>
                      <input type='text' name='course_message' value='$_courseMessage' />
                  </td>
              </tr>
          </table>
          <input type='submit' name='submit_after_editing' value='submit'/>
       </form>";
        
   
}
elseif (isset($_GET['submit_after_editing']))//=='submit')
{
	$courseId       =  $_SESSION['course_id'];
	$courseTiming   =  $_GET['course_timing'];
	$courseMessage  =  $_GET['course_message'];
	
	require(AT_INCLUDE_PATH . 'classes/sqlutility.class.php');
    
	$sql="UPDATE ".TABLE_PREFIX."bigbluebutton SET  course_timing ='$courseTiming', message ='$courseMessage' WHERE  course_id ='$courseId' ;";
	$result        =  mysql_query($sql, $db);
    if ($result==FALSE)
	    echo "unable to connect to database" ;
	else {
	    ?> 
	    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="form">
 		    <table border="2" class="">
         
 		<?php 
       	echo" <tr>
                  <td>Course timing</td><td><input type='text' name='course_timing' value='$_courseTiming' hidden/>$_courseTiming</td>
              </tr>
              <tr>
             	  <td>Message</td><td><input type='text' name='course_message' value='$_courseMessage' hidden />$_courseMessage</td>
              </tr>       
            "
              
  		?>      
   
		    </table>
  		    <input type="submit" value="Edit" name="Edit_button"/>
		</form>
		<?php 
	}
	
}

elseif ($_GET['Edit_button']!="Edit")
{
    $flag   = FALSE;
	$result = mysql_query("SELECT * FROM ".TABLE_PREFIX."bigbluebutton");
	$row;
	while($row = mysql_fetch_array($result)){
	    if((int)$row[0]==(int)$_SESSION['course_id']){
	   	    $flag = TRUE;
      		break;
        }
    }
   
	if(!$flag) {
	    ?>
	    Set course timing and message<br><br>
	  	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="form">
            <table border="2" >
                <tr>
                    <td>Course timing</td>
                    <td><input type='hidden' name='create_classroom' value="checked"><input type="text" name="course_timing" /> </td>
                </tr>
                <tr>
                    <td>Message</td>
                    <td><input type="text" name="course_message" /> </td>
        	    </tr>
	        </table>
            <input type="submit" value="submit" name='submit_button'/>
        </form>
    	<?php
	}
	else {
	    echo "Welcome to BBB <br><br>";
	
 	?> 
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="form">
 	    <table border="2" >
         
 		<?php 
       	echo" <tr>
                  <td WIDTH='200'>Course timing</td><td><input type='text' name='course_timing' value='$row[1]'  hidden/>$row[1]</td>
              </tr>
              <tr>
             	  <td>Message</td><td><input type='text' name='course_message' value='$row[2]'  hidden />$row[2]</td>
              </tr>       
            "             
  		?>      
   		</table>
  		<input type="submit" value="Edit" name="Edit_button"/>
	</form>
	<?php 
     }
}

    echo"</br></br></br><a href='$bbb_joinURL' target='_blank'><b>Click here</b></a> to go to BigBlueButtton classroom.</div>"    
?> 

<?php  require (AT_INCLUDE_PATH.'footer.inc.php'); ?>