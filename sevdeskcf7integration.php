<?php

/*
  Plugin Name: SevDesk Contact Form 7 Automatisierung
  Description: Dieses Plugin integriert Contact Form 7 mit deiner SevDesk Buchhaltung. Es überträgt bereits eingegebene Daten automatisch an SevDesk.
  Version: 1.0
 */

function getCurl() {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    return $curl;
}

function onFormSubmit() {


var_dump($_POST);



    $id = $_POST['_wpcf7'];

    $Masterkurs = get_option('Masterkurs_' . $id);
    $PreisMaster = get_option('PreisMaster_' . $id);
    $yourName = get_option('your-name_' . $id);
    $nachname = get_option('nachname_' . $id);
    $Firma = get_option('Firma_' . $id);
    $Adresse = get_option('Adresse_' . $id);
    $yourEmail = get_option('your-email_' . $id);
    $Rechnungsadresse = get_option('Rechnungsadresse_' . $id);
    $Ort = get_option('Ort_' . $id);



    if (isset($_POST[$yourName]) && isset($_POST[$nachname]) && isset($_POST[$yourEmail])) {




        $first_name = $_POST[$yourName];
        $last_name = $_POST[$nachname];
        $company = $_POST[$Firma];
        $street_name_number = $_POST[$Adresse];
        $email = $_POST[$yourEmail];
        $Rechnungsadresse = $_POST[$Rechnungsadresse];

        $code = trim($_POST['Ort']);
        $code = explode(',', $code);
        if (count($code) == 1) {
            $code = $code[0];
            $code = explode(' ', $code);
        }

        $zip = $code[0];
        array_shift($code);
        $city = implode(' ', $code);

        $zip_code = $zip;

        $field1 = $_POST[$Masterkurs];
        $field2 = $_POST[$PreisMaster];



        $api_url = 'https://my.sevdesk.de/api/v1';
        $api_key = get_option('sevdesk_api_key');

        $curl = getCurl();


        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));



        curl_setopt($curl, CURLOPT_URL, $api_url . '/SevUser/?token=' . $api_key);
        $sevUser = json_decode(curl_exec($curl));

        curl_setopt($curl, CURLOPT_URL, $api_url . '/Contact/?limit=10000&depth=1&token=' . $api_key);
        $contacts = json_decode(curl_exec($curl));

        if (isset($contacts->status) && $contacts->status == 401) {
            return;
        }



        curl_setopt($curl, CURLOPT_URL, $api_url . '/Invoice/?limit=10000&token=' . $api_key);
        $invoices = json_decode(curl_exec($curl));
        var_dump($invoices);
        die();




        $name = $first_name . ' ' . $last_name;
        $new = true;
        $id = false;

        foreach ($contacts->objects as $cont) {
            if (trim($company) && $cont->name == $company) {
                $new = false;
                $id = $cont->id;
                break;
            } elseif (trim($company) == "" && $cont->name2 == $name) {
                $new = false;
                $id = $cont->id;
                break;
            }
        }




        if ($new) {
            /* get categories */
            curl_setopt($curl, CURLOPT_URL, $api_url . '/Category/?limit=10000&token=' . $api_key);
            $categories = json_decode(curl_exec($curl));



            /* create new contact! */

            $cat = new stdClass();
            $cat_id = $categories->objects[0]->id;
            $cat_name = $categories->objects[0]->objectName;


            curl_setopt($curl, CURLOPT_POST, true);

            if (trim($company)) {
                curl_setopt($curl, CURLOPT_URL, $api_url . '/Contact/?status=1000&address[country][code]=de&address[country][code]=germany&name=' . urlencode($company) . '&address[zip]=' . urlencode($zip_code) . '&address[city]=' . urlencode($city) . '&address[street]=' . urlencode($street_name_number) . '&name2=' . urlencode($name) . '&category[id]=' . $cat_id . '&category[objectName]=' . $cat_name . '&token=' . $api_key);
            } else {
                curl_setopt($curl, CURLOPT_URL, $api_url . '/Contact/?status=1000&address[country][code]=de&address[country][code]=germany&name2=' . urlencode($company) . '&address[zip]=' . urlencode($zip_code) . '&address[city]=' . urlencode($city) . '&address[street]=' . urlencode($street_name_number) . '&name2=' . urlencode($name) . '&surename=' . urlencode($first_name) . '&familyname=' . urlencode($last_name) . '&category[id]=' . $cat_id . '&category[objectName]=' . $cat_name . '&token=' . $api_key);
            }

            $person = json_decode(curl_exec($curl));

            $id = $person->objects->id;

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $api_url . '/Contact/' . $id . '/addEmail/?key=1&value=' . $email . '&token=' . $api_key);
            $ca = json_decode(curl_exec($curl));


            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $api_url . '/ContactAddress/?contact[objectName]=Contact&contact[id]=' . $id . '&country[id]=1&country[objectName]=StaticCountry&zip=' . urlencode($zip_code) . '&city=' . urlencode($city) . '&street=' . urlencode($street_name_number) . '&token=' . $api_key);
            $ca = json_decode(curl_exec($curl));
        }




        if (trim($company)) {
            $name = $company;
        } else {
            $name = $first_name . ' ' . $last_name;
        }

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $api_url . '/Invoice/'
                . '?header=' . $name
                . '&invoiceNumber=IV-' . date('Y_m_d_H_i_s')
                . '&invoiceType=RE'
                . '&headText=' . urlencode($Rechnungsadresse)
                . '&addressName=' . urlencode($street_name_number . "\n" . $zip_code . ' ' . $city)
                . '&invoiceDate=' . date('Y-m-d') . 'T' . date('H:i:s')
                . '&timeToPay=7'  
                . '&contactPerson[id]=' . $sevUser->objects[0]->id
                . '&contactPerson[objectName]=SevUser'
                . '&contact[id]=' . $id . ''
                . '&contact[objectName]=Contact'
                . '&discount='
                . '&discountTime='
                . '&taxRate=0%'
                . '&taxText='
                . '&taxType='
                . '&status=100'
                . '&smallSettlement='
                . '&currency=EUR'
                . '&token=' . $api_key);
        $invoice = json_decode(curl_exec($curl));

        $price = explode('€', $field1);
        if (isset($price[1])) {
            $price = $price[1];
            $price = explode(',', $price);
            $price = str_replace('.', '', trim($price[0]));
        } else {
            $price = 0;
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $api_url . '/InvoicePos/?invoice[id]=' . $invoice->objects->id . '&invoice[objectName]=Invoice&name=' . urlencode($field1) . '&quantity=1&price=' . $price . '&unity[id]=1&unity[objectName]=Unity&taxRate=&token=' . $api_key);
        $ca = json_decode(curl_exec($curl));

        $price = explode('€', $field2);
        if (isset($price[1])) {
            $price = $price[1];
            $price = explode(',', $price);
            $price = str_replace('.', '', trim($price[0]));
        } else {
            $price = 0;
        }

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $api_url . '/InvoicePos/?invoice[id]=' . $invoice->objects->id . '&invoice[objectName]=Invoice&name=' . urlencode($field2) . '&quantity=1&price=' . $price . '&unity[id]=1&unity[objectName]=Unity&taxRate=&token=' . $api_key);
        $ca = json_decode(curl_exec($curl));
    }
}

add_action('wpcf7_mail_sent', 'onFormSubmit', 10, 2);


add_action('init', function() {
    if (isset($_GET['test_api'])) {
        $_POST['Masterkurs'] = 'M1-2019 / Start 06. September 2019 / 20 Seminartage';
        $_POST['PreisMaster'] = '€ 3.040,- (M1-2019 Frühbucher bei Anmeldung bis 06.06.2019)';
        $_POST['your-name'] = 'Cezary';
        $_POST['nachname'] = 'Testing';
        $_POST['Firma'] = '';
        $_POST['Adresse'] = 'Gaikowa 11';
        $_POST['Ort'] = '87-100 Toruń';
        $_POST['your-email'] = 'testing@testing.com';
        $_POST['Rechnungsadresse'] = 'Test Test Test';
        $_POST['_wpcf7'] = '5';
        onFormSubmit();
        die();
    }
});


function companies_metabox($post) {
    ?>

    <?php

}

class BackendPanel {

    public function adminMenu() {

        add_menu_page('SevDesk Config', 'SevDesk Config', 'manage_options', 'sevdesk_config', array($this, 'settings'));
    }

    public function settings() {
        if (isset($_POST['save'])) {
            foreach ($_POST as $key => $value) {
                update_option($key, stripslashes($value));
            }
        }
        require_once dirname(__FILE__) . '/tmpl/theme-panel.php';
    }

}

function prepare_backendpanel_plugin() {
    $backendPanel = new BackendPanel();
    add_action('admin_menu', array($backendPanel, 'adminMenu'));
}

add_action('init', 'prepare_backendpanel_plugin');
