<?php

/**
* @desc Prints an actual wwassignment with an iframe to WeBWorK.
*/

// global database object
global $DB,$OUTPUT,$PAGE;

require_once("../../config.php");
require_once("locallib.php");



$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // NEWMODULE ID

if($id) {
    if (! $cm = $DB->get_record("course_modules", array( "id"=>$id ))) {
        print_error("Course Module ID was incorrect");
    }
    
    if (! $course = $DB->get_record("course", array( "id"=>$cm->course ))) {
        print_error("Course is misconfigured");
    }
    if (! $wwassignment = $DB->get_record("wwassignment", array( "id"=>$cm->instance ))) {
        print_error("Course module is incorrect");
    }
} else {
    
    if (! $wwassignment = $DB->get_record("wwassignment", array( "id"=>$a ))) {
        print_error("Course module is incorrect");
    }
    if (! $course = $DB->get_record("course", array("id"=>$wwassignment->course ))) {
        print_error("Course is misconfigured");
    }
    if (! $cm = get_coursemodule_from_instance("wwassignment", $wwassignment->id, $course->id)) {
        print_error("Course Module ID was incorrect");
    }  
}

//catch the guests
global $USER;
if($USER->username == 'guest') {  # this allows guests to view webwork (signed in as user guest)
    #FIXME  -- replace this with a method that uses the automatic guest sign in on webwork.
    // print_error('Guests cannot view WeBWorK Problem Sets');
}

//force login
$courseid = $course->id;
$wwassignmentid = $wwassignment->id;
require_login($courseid);

// set page url
$PAGE->set_url('/mod/wwassignment/view.php',array('id'=>$cm->id));
$PAGE->set_pagelayout('login');

//webwork code
$wwcoursename = _wwassignment_mapped_course($courseid,false);
$wwusername = $USER->username;
$wwsetname = $wwassignment->webwork_set;
_wwassignment_mapcreate_user($wwcoursename,$wwusername);
_wwassignment_mapcreate_user_set($wwcoursename,$wwusername,$wwsetname);

$wwkey = _wwassignment_login_user($wwcoursename,$wwusername);
$wwsetlink = _wwassignment_link_to_set_auto_login($wwcoursename,$wwsetname,$wwusername,$wwkey);

add_to_log($course->id, "wwassignment", "view", "view.php?id=$cm->id", "$wwassignmentid",_wwassignment_cmid());

/// Print the page header

#if ($course->category) {
#    $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
#}

$strwwassignments = get_string("modulenameplural", "wwassignment");
$strwwassignment  = get_string("modulename", "wwassignment");

# print_header("$course->shortname: $wwassignment->name", "$course->fullname", "$navigation <a href='index.php?id=$course->id'>$strwwassignments</a> -> $wwassignment->name", "", "", true, update_module_button($cm->id, $course->id, $strwwassignment), navmenu($course, $cm));

$PAGE->set_heading("$course->fullname");
$PAGE->set_title("$course->shortname: $wwassignment->name");
$PAGE->set_cacheable(true);
$PAGE->set_focuscontrol("");
$PAGE->set_button(update_module_button($cm->id, $course->id, $strwwassignment));
$PAGE->navbar->add($strwwassignments,"index.php?id=$course->id");
$PAGE->navbar->add($wwassignment->name);
echo $OUTPUT->header();


/// Print the main part of the page



// Print webwork in iframe and link to escape to have webwork in a single window
print("<p style='font-size: smaller; color: #aaa;'>" . get_string("iframeNoShow-1", "wwassignment")
      . "<a href='$wwsetlink'>" . get_string("iframeNoShow-2", "wwassignment")
      ."</a><p align='center'></iframe></p>\n"
      );
print("<iframe id='wwPage' src='$wwsetlink' frameborder='0' "
      . "width='".$CFG->wwassignment_iframewidth."' "
      . "height='".$CFG->wwassignment_iframeheight."' scrolling='no' >"
      );

$context = get_context_instance(CONTEXT_COURSE, $course->id);
print("<script>ww.Init(".has_capability('moodle/course:manageactivities',$context).")</script></iframe>");


/// Finish the page
echo $OUTPUT->footer();

?>
