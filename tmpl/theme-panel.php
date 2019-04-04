<?php
global $wpdb;
?>
<div id="config-sevdesk">
    <?php if (isset($_POST['save'])): ?>
        <p class="save">The changes has been saved!</p>
    <?php endif ?>
    <form action="" method="post">
        <div class="one-segment">
            <label for="sevdesk_api_key">API Key</label>
            <input type="text" name="sevdesk_api_key" id="sevdesk_api_key" value="<?php echo get_option('sevdesk_api_key') ?>">
        </div>
        <?php
        $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1); 
        $cf7Forms = get_posts( $args );
        if(!empty($cf7Forms))
        {
            /*
Masterkurs
PreisMaster
your-name
nachname
Firma
Adresse
your-email
Rechnungsadresse
             *              */
        ?>
        <div class="one-segment">
            <label>Fields Mapping</label>            
        </div>
        <?php foreach($cf7Forms as $form):
            
            $Masterkurs = get_option('Masterkurs_'.$form->ID);
            $PreisMaster = get_option('PreisMaster_'.$form->ID);
            $yourName = get_option('your-name_'.$form->ID);
            $nachname = get_option('nachname_'.$form->ID);
            $Firma = get_option('Firma_'.$form->ID);
            $Adresse = get_option('Adresse_'.$form->ID);
            $yourEmail = get_option('your-email_'.$form->ID);
            $Rechnungsadresse = get_option('Rechnungsadresse_'.$form->ID);
            $Ort = get_option('Ort_'.$form->ID);
            
            
            ?>
        <div class="one-segment-form">
            <p><b>Forms ID:</b> <?php echo $form->ID ?></p>
            <p><span>Masterkurs</span> <input name="Masterkurs_<?php echo $form->ID ?>" value="<?php echo $Masterkurs ?>" /></p>
            <p><span>PreisMaster</span> <input name="PreisMaster_<?php echo $form->ID ?>" value="<?php echo $PreisMaster  ?>" /></p>
            <p><span>your-name</span> <input name="your-name_<?php echo $form->ID ?>" value="<?php echo $yourName  ?>" /></p>
            <p><span>nachname</span> <input name="nachname_<?php echo $form->ID ?>" value="<?php echo $nachname  ?>" /></p>
            <p><span>Firma</span> <input name="Firma_<?php echo $form->ID ?>" value="<?php echo  $Firma ?>" /></p>
            <p><span>Adresse</span> <input name="Adresse_<?php echo $form->ID ?>" value="<?php echo $Adresse  ?>" /></p>
            <p><span>Ort</span> <input name="Ort_<?php echo $form->ID ?>" value="<?php echo $Ort  ?>" /></p>
            <p><span>your-email</span> <input name="your-email_<?php echo $form->ID ?>" value="<?php echo $yourEmail  ?>" /></p>
            <p><span>Rechnungsadresse</span> <input name="Rechnungsadresse_<?php echo $form->ID ?>" value="<?php echo $Rechnungsadresse  ?>" /></p>
            <p>
                <span>Header Text</span>
            </p>
        </div>
        <?php endforeach ?>
        <?php } ?>
        

        <div class="one-segment">

            <input type="submit" class="button primary" name='save' value="save">
        </div>
    </form>
</div>

<style>

    #config-sevdesk
    {
        padding-top: 50px;
    }

    .one-segment
    {
        margin-bottom:20px;
    }

    #sevdesk_api_key
    {
        width: 100%;
        max-width: 350px;
        margin-top: 10px;
        display:block;
    }

    .one-segment label
    {
        font-weight: bold;
        display:block;
    }

    .one-segment-form
    {
        margin-bottom: 30px;
    }
    
    .one-segment-form span
    {
        display: inline-block;
        width: 120px;
    }
    
    .save
    {
        font-size:18px;
        color: #5c5;
        font-weight:bold;
        margin-bottom:20px;
    }

</style>