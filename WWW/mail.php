<?php
require 'lib/phpword/phpoffice/phpword/vendor/autoload.php';
$phpWord = new  \PhpOffice\PhpWord\PhpWord(); 
$_doc = new \PhpOffice\PhpWord\TemplateProcessor('template.docx');
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/
$to = 'office@ukrpersonal.com'; // email
$subject = 'Новая анкета с сайта';       // subject
$headers = 'From: info@ukrpersonal.com' . "\r\n" .
    'Reply-To: info@ukrpersonal.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

// $languages = [
//     'russian' => [
//         'Basic' => 'no',
//         'Elementary' => 'no',
//         'Lower Intermediate' => 'no',
//         'Intermediate' => 'no',
//         'Upper-Intermediate' => 'no',
//         'Advanced' => 'no',
//         'Fluent' => 'no'
//     ],
//     'english' => [
//         'Basic' => 'no',
//         'Elementary' => 'no',
//         'Lower Intermediate' => 'no',
//         'Intermediate' => 'no',
//         'Upper-Intermediate' => 'no',
//         'Advanced' => 'no',
//         'Fluent' => 'no'
//     ]
// ];

function set_mess($variable, $text, $mess)
{
    if (isset($variable) && !empty($variable)) {
        $mess .= "\r\n" . $text . "\r\n" . $variable . "\r\n";
    }
    return $mess;
}

function url()
{
    $base_url = (isset($_SERVER['HTTPS']) &&

        $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';

    $tmpURL = dirname(__FILE__);

    $tmpURL = str_replace(chr(92), '/', $tmpURL);

    $tmpURL = str_replace($_SERVER['DOCUMENT_ROOT'], '', $tmpURL);

    $tmpURL = ltrim($tmpURL, '/');

    $tmpURL = rtrim($tmpURL, '/');

    if (strpos($tmpURL, '/')) {

        $tmpURL = explode('/', $tmpURL);

        $tmpURL = $tmpURL[0];

    }

    if ($tmpURL !== $_SERVER['HTTP_HOST'])
        $base_url .= $_SERVER['HTTP_HOST'] . '/' . $tmpURL . '/';
    else
        $base_url .= $tmpURL . '/';
    return $base_url;
}

/**
 * Convert HTML to MS Word document
 * @name HTML_TO_DOC
 * @version 2.0
 * @author CodexWorld
 * @link https://www.codexworld.com
 */
class HTML_TO_DOC
{
    var $docFile  = '';
    var $title    = '';
    var $htmlHead = '';
    var $htmlBody = '';

    /**
     * Constructor
     *
     * @return void
     */
    function __construct(){
        $this->title = '';
        $this->htmlHead = '';
        $this->htmlBody = '';
    }

    /**
     * Set the document file name
     *
     * @param String $docfile
     */
    function setDocFileName($docfile){
        $this->docFile = $docfile;
        if(!preg_match("/\.doc$/i",$this->docFile) && !preg_match("/\.docx$/i",$this->docFile)){
            $this->docFile .= '.doc';
        }
        return;
    }

    /**
     * Set the document title
     *
     * @param String $title
     */
    function setTitle($title){
        $this->title = $title;
    }

    /**
     * Return header of MS Doc
     *
     * @return String
     */
    function getHeader(){
        $return ='
        <html xmlns:v="urn:schemas-microsoft-com:vml" 
        xmlns:o="urn:schemas-microsoft-com:office:office" 
        xmlns:w="urn:schemas-microsoft-com:office:word" 
        xmlns="http://www.w3.org/TR/REC-html40"> 
         
        <head> 
        <meta http-equiv=Content-Type content="text/html; charset=utf-8"> 
        <meta name=ProgId content=Word.Document> 
        <meta name=Generator content="Microsoft Word 9"> 
        <meta name=Originator content="Microsoft Word 9"> 
        <!--[if !mso]> 
        <style> 
        v\:* {behavior:url(#default#VML);} 
        o\:* {behavior:url(#default#VML);} 
        w\:* {behavior:url(#default#VML);} 
        .shape {behavior:url(#default#VML);} 
        </style> 
        <![endif]--> 
        <title>title</title> 
        </head> 
        <body> 
        ';
        return $return;
    }

    /**
     * Return Document footer
     *
     * @return String
     */
    function getFotter(){
        return "</body></html>";
    }

    /**
     * Create The MS Word Document from given HTML
     *
     * @param String $html :: HTML Content or HTML File Name like path/to/html/file.html
     * @param String $file :: Document File Name
     * @param Boolean $download :: Wheather to download the file or save the file
     * @return boolean
     */
    function createDoc($html, $file, $download = false){
        if(is_file($html)){
            $html = @file_get_contents($html);
        }

        $this->_parseHtml($html);
        $this->setDocFileName($file);
        $doc = $this->htmlBody;

        if($download){
            @header("Cache-Control: ");// leave blank to avoid IE errors
            @header("Pragma: ");// leave blank to avoid IE errors
            @header("Content-type: application/octet-stream");
            @header("Content-Disposition: attachment; filename=\"$this->docFile\"");
            echo $doc;
            return true;
        }else {
            return $this->write_file($this->docFile, $doc);
        }
    }

    /**
     * Parse the html and remove <head></head> part if present into html
     *
     * @param String $html
     * @return void
     * @access Private
     */
    function _parseHtml($html){
        $html = preg_replace("/<!DOCTYPE((.|\n)*?)>/ims", "", $html);
        $html = preg_replace("/<script((.|\n)*?)>((.|\n)*?)<\/script>/ims", "", $html);
        preg_match("/<head>((.|\n)*?)<\/head>/ims", $html, $matches);
        $head = !empty($matches[1])?$matches[1]:'';
        preg_match("/<title>((.|\n)*?)<\/title>/ims", $head, $matches);
        $this->title = !empty($matches[1])?$matches[1]:'';
        $html = preg_replace("/<head>((.|\n)*?)<\/head>/ims", "", $html);
        $head = preg_replace("/<title>((.|\n)*?)<\/title>/ims", "", $head);
        $head = preg_replace("/<\/?head>/ims", "", $head);
        $html = preg_replace("/<\/?body((.|\n)*?)>/ims", "", $html);
        $this->htmlHead = $head;
        $this->htmlBody = $html;
        return;
    }

    /**
     * Write the content in the file
     *
     * @param String $file :: File name to be save
     * @param String $content :: Content to be write
     * @param [Optional] String $mode :: Write Mode
     * @return void
     * @access boolean True on success else false
     */
    function write_file($file, $content, $mode = "w"){
        $fp = @fopen($file, $mode);
        if(!is_resource($fp)){
            return false;
        }
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }
}

$htd = new HTML_TO_DOC();
$message = '';
$position = filter_input(INPUT_POST, 'positions', FILTER_SANITIZE_STRING);
// position
$message = set_mess($position, 'Desired positions', $message);
// full-name
$full_name = filter_input(INPUT_POST, 'full-name', FILTER_SANITIZE_STRING);
$message = set_mess($full_name, 'Full name', $message);

//age-year
$age_year = filter_input(INPUT_POST, 'age-year', FILTER_SANITIZE_STRING);
$message = set_mess($age_year, 'Year', $message);

//age-month
$age_month = filter_input(INPUT_POST, 'age-month', FILTER_SANITIZE_STRING);
$message = set_mess($age_month, 'Month', $message);

//age-day
$age_day = filter_input(INPUT_POST, 'age-day', FILTER_SANITIZE_STRING);
$message = set_mess($age_day, 'Day', $message);

//country
$country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
$message = set_mess($country, 'Country', $message);

//city
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$message = set_mess($city, 'City/ Village', $message);

//marital-status
$marital = filter_input(INPUT_POST, 'marital-status', FILTER_SANITIZE_STRING);
$message = set_mess($marital, 'Marital status', $message);

//children
$children = filter_input(INPUT_POST, 'children', FILTER_SANITIZE_STRING);
$message = set_mess($children, 'Do you have children?', $message);

//children-specify
$children_specify = filter_input(INPUT_POST, 'children-specify', FILTER_SANITIZE_STRING);
$message = set_mess($children_specify, 'If yes please specify *', $message);

//cm
$cm = filter_input(INPUT_POST, 'cm', FILTER_SANITIZE_STRING);
$message = set_mess($cm, 'Height / Weight CM', $message);

//kg
$kg = filter_input(INPUT_POST, 'kg', FILTER_SANITIZE_STRING);
$message = set_mess($kg, 'Height / Weight KG', $message);

//residence
$residence = filter_input(INPUT_POST, 'residence', FILTER_SANITIZE_STRING);
$message = set_mess($residence, 'City of residence', $message);

$location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
//location
$message = set_mess($location, 'Current location', $message);


$nationality = filter_input(INPUT_POST, 'nationality', FILTER_SANITIZE_STRING);
//nationality
$message = set_mess($nationality, 'Nationality', $message);


$citizenship = filter_input(INPUT_POST, 'citizenship', FILTER_SANITIZE_STRING);
//citizenship
$message = set_mess($citizenship, 'Citizenship', $message);


$travel = filter_input(INPUT_POST, 'travel', FILTER_SANITIZE_STRING);
//travel
$message = set_mess($travel, 'Do you have travel passport ?', $message);


// covid
$covid = filter_input(INPUT_POST, 'covid', FILTER_SANITIZE_STRING);
$message = set_mess($covid, 'Have you received COVID vaccine ?', $message);

//phone
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$message = set_mess($phone, 'Mobile number ', $message);

//messenger
$messenger = filter_input(INPUT_POST, 'messenger', FILTER_SANITIZE_STRING);
$message = set_mess($messenger, 'Messenger', $message);

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
$message = set_mess($email, 'E-mail addresses', $message);

//facebook
$facebook = filter_input(INPUT_POST, 'facebook', FILTER_SANITIZE_STRING);
$message = set_mess($facebook, 'Facebook page', $message);

//instagram
$instagram = filter_input(INPUT_POST, 'instagram', FILTER_SANITIZE_STRING);
$message = set_mess($instagram, 'Instagram page', $message);

$message = set_mess('Relative’s contact details in case of emergency', '', $message);
//linkedin
$linkedin = filter_input(INPUT_POST, 'linkedin', FILTER_SANITIZE_STRING);
$message = set_mess($linkedin, 'Linkedin page', $message);


$relations_name = filter_input(INPUT_POST, 'relations-name', FILTER_SANITIZE_STRING);
//relations-name
$message = set_mess($relations_name, 'Name', $message);

$relations_relationship = filter_input(INPUT_POST, 'relations-relationship', FILTER_SANITIZE_STRING);
//relations-relationship
$message = set_mess($relations_relationship, 'Relationship', $message);

$relations_phone = filter_input(INPUT_POST, 'relations-phone', FILTER_SANITIZE_STRING);
//relations-phone
$message = set_mess($relations_phone, 'Mobile number ', $message);

$relations_fathers = filter_input(INPUT_POST, 'relations-fathers', FILTER_SANITIZE_STRING);
//relations-fathers
$message = set_mess($relations_fathers, 'Father’s name', $message);

$relations_mothers = filter_input(INPUT_POST, 'relations-mothers', FILTER_SANITIZE_STRING);
//relations-mothers
$message = set_mess($relations_mothers, 'Mother’s name', $message);

$relocated_types_array = array(
    'Qatar' => " ",
    'UAE' => " ",
    'Bahrain' => " ",
    'Oman' => " ",
    'Saudi Arabia' => " ",
    'Jordan' => " ",
    'Bulgaria' => " ",
);
$relocated_array = array();
//relocate[]
$relocate = isset($_POST['relocate']) ? $_POST['relocate'] : null;
if (isset($_POST['relocate'])) {
    $relocate_count = count($relocate);
    $clone_relocated_types_array = $relocated_types_array;
    for ($i = 0; $i < $relocate_count; $i++) {
        foreach ($clone_relocated_types_array as $key => $value) {
            if ($key === $relocate[$i]) {
                $clone_relocated_types_array[$key] = "Yes";
            }
        }
    }
    array_push($relocated_array, $clone_relocated_types_array);
}

$message = set_mess(implode(',', $relocate), 'Preferred countries to relocate and work', $message);


$message = set_mess('Education', '', $message);
$educations = array();
$education_number = filter_input(INPUT_POST, 'education-count', FILTER_SANITIZE_NUMBER_INT);
for ($i = 1; $i <= $education_number; $i++) {
    $education_array = array();
    $education_certificate = filter_input(INPUT_POST, 'education-certificate-' . $i, FILTER_SANITIZE_STRING);
    // education-certificate-1
    $message = set_mess($education_certificate, 'Certificate / Degree ', $message);

    $education_collage = filter_input(INPUT_POST, 'education-collage-' . $i, FILTER_SANITIZE_STRING);
    // education-collage-1
    $message = set_mess($education_collage, 'College/ University ', $message);

    $education_spec = filter_input(INPUT_POST, 'education-spec-' . $i, FILTER_SANITIZE_STRING);
    // education-spec-1
    $message = set_mess($education_spec, 'Area of Specialization ', $message);

    $education_years = filter_input(INPUT_POST, 'education-years-' . $i, FILTER_SANITIZE_STRING);
    // education-years-1
    $message = set_mess($education_years, 'Years started-completed ', $message);
    $education_array['education_certificate'] = $education_certificate;
    $education_array['education_collage'] = $education_collage;
    $education_array['education_spec'] = $education_spec;
    $education_array['education_years'] = $education_years;
    array_push($educations, $education_array);
}

$trainings = array();
$training_number = filter_input(INPUT_POST, 'training-count', FILTER_SANITIZE_NUMBER_INT);
$message = set_mess('Seminars / training attended/ Short Courses taken, and date attended', '', $message);

for ($i = 1; $i <= $training_number; $i++) {
    $training_array = array();
    // training-year-1
    // training-name-1
    // training-period-1
    $training_year = filter_input(INPUT_POST, 'training-year-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($training_year, 'Year ', $message);
    $training_name = filter_input(INPUT_POST, 'training-name-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($training_name, 'Name ', $message);
    $training_period = filter_input(INPUT_POST, 'training-period-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($training_period, 'Period of attending', $message);
    $training_array['training_year'] = $training_year;
    $training_array['training_name'] = $training_name;
    $training_array['training_period'] = $training_period;
    array_push($trainings, $training_array);
}

$tattoos = filter_input(INPUT_POST, 'tattoos', FILTER_SANITIZE_STRING);
// // tattoos
$message = set_mess($tattoos, 'Do you have any tattoos or piercing ?', $message);

$message = set_mess('Work experience', '', $message);
$experiences = array();
$experience_number = filter_input(INPUT_POST, 'experience-count', FILTER_SANITIZE_NUMBER_INT);
for ($i = 1; $i <= $experience_number; $i++) {
    $experience = array();
    // experience-position-1
    // experience-company-1
    // experience-date-1
    // experience-respons-1
    $exp_position = filter_input(INPUT_POST, 'experience-position-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($exp_position, 'Position', $message);
    $exp_company = filter_input(INPUT_POST, 'experience-company-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($exp_company, 'Company’s name, location', $message);
    $exp_date = filter_input(INPUT_POST, 'experience-date-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($exp_date, 'Date (started-left)', $message);
    $exp_respons = filter_input(INPUT_POST, 'experience-respons-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($exp_respons, 'Responsibilities', $message);
    $experience_array['exp_position'] = $exp_position;
    $experience_array['exp_company'] = $exp_company;
    $experience_array['exp_date'] = $exp_date;
    $experience_array['exp_respons'] = $exp_respons;
    array_push($experiences, $experience_array);
}

$message = set_mess('Computer programs you work with', '', $message);
$hospitality = filter_input(INPUT_POST, 'hospitality', FILTER_SANITIZE_STRING);
$message = set_mess($hospitality, 'Hospitality programs:', $message);

// hospitality
$finance = filter_input(INPUT_POST, 'finance', FILTER_SANITIZE_STRING);
$message = set_mess($finance, 'Finance programs:', $message);

// finance
$travels = filter_input(INPUT_POST, 'travel-program', FILTER_SANITIZE_STRING);
$message = set_mess($travels, 'Travel & booking programs:', $message);
// travel
$graphics = filter_input(INPUT_POST, 'graphics', FILTER_SANITIZE_STRING);
$message = set_mess($graphics, 'Graphics & Design programs:', $message);

// graphics
$software = filter_input(INPUT_POST, 'software', FILTER_SANITIZE_STRING);

// software
$message = set_mess($software, 'Other software:', $message);


$licence = filter_input(INPUT_POST, 'licence', FILTER_SANITIZE_STRING);
$message = set_mess($licence, 'Do you have driver licence', $message);

// licence
$licence_description = filter_input(INPUT_POST, 'licence-description', FILTER_SANITIZE_STRING);
// licence-description
$message = set_mess($licence_description, 'If yes, please specify which category?', $message);


$language_number = filter_input(INPUT_POST, 'language-count', FILTER_SANITIZE_NUMBER_INT);
$message = set_mess('Languages Known', '', $message);
$choised_languages = array();
for ($i = 1; $i <= $language_number; $i++) {
    $language_name = filter_input(INPUT_POST, 'language-name-' . $i, FILTER_SANITIZE_STRING);
    $language = filter_input(INPUT_POST, 'language-' . $i, FILTER_SANITIZE_STRING);
    $message = set_mess($language_name . ' - ' . $language, '', $message);
    array_push($choised_languages, array($language_name => $language));
}

$language_types_array = array(
    'Language' => ' ',
    'Basic' => ' ',
    'Elementary' => ' ',
    'Lower Intermediate' => ' ',
    'Intermediate' => ' ',
    'Upper-Intermediate' => ' ',
    'Advanced' => ' ',
    'Fluent' => ' ',
);

$languages = array();
$choised_languages_count = count($choised_languages);
for ($i = 0; $i < $choised_languages_count; $i++) {
    foreach ($choised_languages[$i] as $key => $value) {
        $copy_language_types_array = $language_types_array;
        $copy_language_types_array['Language'] = $key;
        foreach ($copy_language_types_array as $key1 => $value1) {
            if ($key1 === $value) {
                $copy_language_types_array[$key1] = 'Yes';
            }
        }
        array_push($languages, $copy_language_types_array);
    }
}

// language-name-1
// language-1

$feedback = filter_input(INPUT_POST, 'feedback', FILTER_SANITIZE_STRING);
$message = set_mess($feedback, 'How did you know about us?', $message);

$feedback_types_array = array(
    'Google search' => ' ',
    'Facebook' => ' ',
    'Instagram' => ' ',
    'Friends recommendation' => ' ',
    'Other sources of employment search' => ' ',
);

$feedbacks = array();
$copy_feedback_types_array = $feedback_types_array;

foreach ($copy_feedback_types_array as $key => $value) {
    if ($key !== $feedback) {
        $copy_feedback_types_array[$key] = ' ';
    } else {
        $copy_feedback_types_array[$key] = "Yes";
    }
}
array_push($feedbacks, $copy_feedback_types_array);

// feedback
$contract = filter_input(INPUT_POST, 'contract', FILTER_SANITIZE_STRING);
$message = set_mess($contract, 'I agree to sign a contract with the employer for at least 24 months', $message);
// contract


$message = set_mess('I confirm that all information given on this resume is true', '', $message);
$conf_name = filter_input(INPUT_POST, 'confirm-name', FILTER_SANITIZE_STRING);
$message = set_mess($conf_name, 'Please type first and last name', $message);

//confirm-name
$conf_date = filter_input(INPUT_POST, 'confirm-date', FILTER_SANITIZE_STRING);
$message = set_mess($conf_date, 'Date ', $message);
//confirm-date

// tattoos-file
$tattoos_file = isset($_FILES['tattoos-file']) ? $_FILES['tattoos-file'] : null;
// covid-file
$covid_file = isset($_FILES['covid-file']) ? $_FILES['covid-file'] : null;
//passport
$passport_file = isset($_FILES['passport']) ? $_FILES['passport'] : null;
//smile-file
$smile_file = isset($_FILES['smile-file']) ? $_FILES['smile-file'] : null;

$direct = date('Y-m-d-h-i-s');
$structure = __DIR__ . '/cv/' . $direct . '/';
$url_dir = url() . 'cv/' . $direct . '/';

if (is_array($tattoos_file) || is_array($covid_file) || is_array($passport_file) || is_array($smile_file)) {

    if (!mkdir($structure, 0777, true)) {
        $response['message'] = 'Directory not create';
        echo json_encode($response, JSON_PRETTY_PRINT);
        die;
    } else {
        if (isset($smile_file['tmp_name'])) {
            $extension = pathinfo($smile_file['name'], PATHINFO_EXTENSION);
            $new_name = 'smile.' . $extension;
            $upload_file = $structure . basename($new_name);
            move_uploaded_file($smile_file['tmp_name'], $upload_file);
            $message = set_mess($url_dir . $new_name,
                'Smile photo', $message);

        }

        if (isset($tattoos_file['tmp_name'])) {
            $extension = pathinfo($tattoos_file['name'], PATHINFO_EXTENSION);
            $new_name = 'tattoo.' . $extension;
            $upload_file = $structure . basename($new_name);
            move_uploaded_file($tattoos_file['tmp_name'], $upload_file);
            $message = set_mess($url_dir . $new_name,
                'Tattoos photo', $message);
        }

        if (isset($passport_file['tmp_name'])) {
            $extension = pathinfo($passport_file['name'], PATHINFO_EXTENSION);
            $new_name = 'passport.' . $extension;
            $upload_file = $structure . basename($new_name);
            move_uploaded_file($passport_file['tmp_name'], $upload_file);
            $message = set_mess($url_dir . $new_name,
                'Passport photo', $message);
        }

        if (isset($covid_file['tmp_name'])) {
            $extension = pathinfo($covid_file['name'], PATHINFO_EXTENSION);
            $new_name = 'covid.' . $extension;
            $upload_file = $structure . basename($new_name);
            move_uploaded_file($covid_file['tmp_name'], $upload_file);
            $message = set_mess($url_dir . $new_name,
                'Covid photo', $message);
        }

    }
}

$_doc->setValue('desiredpos', $position); 
$_doc->setValue('fullname', $full_name); 
$_doc->setValue('birth_year', $age_year); 
$_doc->setValue('birth_month', $age_month); 
$_doc->setValue('birth_day', $age_day); 
$_doc->setValue('birth_country', $country); 
$_doc->setValue('birth_city', $city); 
$_doc->setValue('marital_status', $marital); 
$_doc->setValue('children', $children . " " . $children_specify); 
$_doc->setValue('height', $cm); 
$_doc->setValue('weight', $kg); 
$_doc->setValue('res_city', $residence); 
$_doc->setValue('current_location', $location); 
$_doc->setValue('nationality', $nationality); 
$_doc->setValue('citizenship', $citizenship); 
$_doc->setValue('is_have_travel_passport', $travel); 
$_doc->setValue('is_have_covid_vaccine', $covid); 
$_doc->setValue('mobile', $phone); 
$_doc->setValue('messenger', $messenger); 
$_doc->setValue('email', $email); 
$_doc->setValue('facebook', $facebook); 
$_doc->setValue('instagram', $instagram); 
$_doc->setValue('linkedin', $linkedin); 
$_doc->setValue('e_name', $relations_name); 
$_doc->setValue('e_relationship', $relations_relationship); 
$_doc->setValue('e_mobile', $relations_phone); 
$_doc->setValue('e_father_name', $relations_fathers); 
$_doc->setValue('e_mother_name', $relations_mothers); 
$_doc->setValue('relocate_qatar', $relocate); 
$_doc->setValue('relocate_uae', $relocate); 
$_doc->setValue('relocate_bahrain', $relocate); 
$_doc->setValue('relocate_oman', $relocate); 
$_doc->setValue('relocate_sa', $relocate); 
$_doc->setValue('relocate_jordan', $relocate); 
$_doc->setValue('relocate_bulgaria', $relocate); 
$_doc->cloneRowAndSetValues('Qatar', $relocated_array);
$_doc->cloneRowAndSetValues('education_certificate', $educations);
$_doc->cloneRowAndSetValues('training_year', $trainings);
$_doc->setValue('tattoos', $tattoos); 
$_doc->cloneBlock('work_experience', 0, true, false, $experiences);
$_doc->cloneBlock('languages', 0, true, false, $languages);
$_doc->setValue('hospitality', $hospitality); 
$_doc->setValue('finance', $finance); 
$_doc->setValue('travel', $travels); 
$_doc->setValue('design', $graphics); 
$_doc->setValue('other', $software); 
$_doc->cloneRowAndSetValues('Google search', $feedbacks);

$_doc->setValue('driver_license', $licence); 
$_doc->setValue('driver_license_category', $licence_description);
$_doc->setValue('sign', $conf_name); 
$_doc->setValue('sign_date', $conf_date); 

$img_Dir_Str = "/files/";
$img_Dir = $_SERVER['DOCUMENT_ROOT']."/". $img_Dir_Str; 
@mkdir($img_Dir, 0777);
$name_dock = 'cv.docx';
$file = $name_dock;
$_doc->saveAs( $structure.$file);

$htmlContent = $message;

// $htd->createDoc($htmlContent, $structure.$name_dock);
$message = set_mess($url_dir.$name_dock, 'CV File', $message);

// var_dump($message);
$response = array(
    'success' => false,
    'message' => 'An error occurred while sending the letter'
);

$res = mail($to, $subject, $message, $headers);

if ($res) {
    $response['success'] = true;
    $response['message'] = 'Message send success';
    echo json_encode($response, JSON_PRETTY_PRINT);
    die;
}

$response['message'] = 'Message sent error';
echo json_encode($response, JSON_PRETTY_PRINT);
die;
