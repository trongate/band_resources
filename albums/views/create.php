<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Album Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('Album Title');
        echo form_input('album_title', $album_title, array("placeholder" => "Enter Album Title"));
        echo form_label('Date Released');
        $attr = array("class"=>"date-picker", "autocomplete"=>"off", "placeholder"=>"Select Date Released");
        echo form_input('date_released', $date_released, $attr);
        echo form_label('Label');
        echo form_input('label', $label, array("placeholder" => "Enter Label"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>