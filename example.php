<?
/*
    **********************************************************
    ** This is the SlapTight Example Program. This Example  **
    ** Program attemps to walk you though the use and       **
    ** application of slapTight in your project.            **
    **********************************************************

Thanks for checking out slapTight, Lets get right to it. 
You Should probably take a look at the example data sql file included with the project, that will shed a bit of light on what is going on here. Start with the following steps:
1. make a new database.
2. load the database with the included .sql file. (mysql -u root -p -D database name < slapTightTest.sql)
3. update slapTight's config.ini with your database settings.
4. view this file in your browser, as well as with a text editor, so you can follow along. 

*/

include 'slapTight.class.php';

//lets set the selected variable to the code value from get, if it is there.
$selected = $_GET['code'];
//This is the sql query, and the only sql query that will be used manually by the programmer.
$sql = "SELECT * FROM people";
//execute the sql query and make an array of slapTight row objects as the result
$people = slapTight::select("people", $sql);
//lets cycle through them all to build the list and some other operations.
foreach ($people as $key=>$data) {
        //if there is no record selecrted, set the default to the first in the list
        if (!isset($selected)) {
            $selected = $data->id;
        }
        //if we have recieved a submission of data from the form, go ahead and make the changes.
        //note that there is no sql necessary to update the database with this information.
        //also note that we are setting the data AFTER the initial query, and still the data
        // that is presented will be the new data from the post. 
        if (($selected == $data->id) && ($_POST['submitted'] == 1)) {
            $data->first_name = $_POST['first_name'];
            $data->last_name = $_POST['last_name'];
            $data->title = $_POST['title'];
            $data->company = $_POST['company'];
            $data->phone = $_POST['phone'];
            $data->fax = $_POST['fax'];
        }
        //if this is the record that we were looking at then make it the selected option.
        if ($selected == $data->id) {
            $optionSelected = "selected";
        } else {
            $optionSelected = "";
        }
        //build the option list.
        $options .= "<option value='".$data->id."' $optionSelected>".$data->last_name.", ".$data->first_name."</option>";
        //lets select the default person record, the one we are going to be working with.
        if ($selected == $data->id) {
            $person = $data;
        }
}

//-------------------------------
// Begin output 
//-------------------------------
$out = "<HTML>
        <HEAD>
            <title>SlapTighTest</title>
        </head>";
$out .= "<body>";
//Provide a little javascript to move between the records easily.
$out .= "<b>Person: </b><select onChange='location.href=\"".$_SERVER['PHP_SELF']."?code=\"+this.options[this.selectedIndex].value'>";
$out .= $options;
$out .= "</select>";
$out .= "<hr>";
$out .= "<form method='post' action='".$_SERVER['PHP_SELF']."?code=".$selected."'>";
$out .= "<table cellspacing='0' cellpadding='5' border='0'>";
$out .= "<tr><td><b>First Name: </b></td>";
//If the query is set to the LIVE state, then each time you retrieve the data from the object like this
// it will do another query to make sure that the latest data is returned.
$out .= "<td><input type='text' name='first_name' value='".$person->first_name."'></td>";
$out .= "<tr><td><b>Last Name: </b></td>";
$out .= "<td><input type='text' name='last_name' value='".$person->last_name."'></td>";
$out .= "<tr><td><b>Title: </b></td>";
$out .= "<td><input type='text' name='title' value='".$person->title."'></td>";
$out .= "<tr><td><b>Company: </b></td>";
$out .= "<td><input type='text' name='company' value='".$person->company."'></td>";
$out .= "<tr><td><b>Phone: </b></td>";
$out .= "<td><input type='text' name='phone' value='".$person->phone."'></td>";
$out .= "<tr><td><b>Fax: </b></td>";
$out .= "<Td><input type='text' name='fax' value='".$person->fax."'></td>";
$out .= "<tr><td></td><td><input type='hidden' name='submitted' value='1'><input type='submit' value='Submit'></td>";
$out .= "</table></form>";
$out .= "</body></html>";

//go ahead and echo the output.
echo $out;
?>
