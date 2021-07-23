<h1><?= $headline ?></h1>
<?php
flashdata();
echo '<p>'.anchor('albums/create', 'Create New Album Record', array("class" => "button")).'</p>'; 
echo Pagination::display($pagination_data);
if (count($rows)>0) { ?>
    <table id="results-tbl">
        <thead>
            <tr>
                <th colspan="5">
                    <div>
                        <div><?php
                        echo form_open('albums/manage/1/', array("method" => "get"));
                        echo form_input('searchphrase', '', array("placeholder" => "Search records..."));
                        echo form_submit('submit', 'Search', array("class" => "alt"));
                        echo form_close();
                        ?></div>
                        <div>Records Per Page: <?php
                        $dropdown_attr['onchange'] = 'setPerPage()';
                        echo form_dropdown('per_page', $per_page_options, $selected_per_page, $dropdown_attr); 
                        ?></div>

                    </div>                    
                </th>
            </tr>
            <tr>
                <th>Album Title</th>
                <th>Date Released</th>
                <th>Label</th>
                <th>Album Cover</th>
                <th style="width: 20px;">Action</th>            
            </tr>
        </thead>
        <tbody>
            <?php 
            $attr['class'] = 'button alt';
            foreach($rows as $row) { 
                $picture_path = BASE_URL.'albums_module/images/albums_pics_thumbnails/'.$row->id.'/'.$row->picture;
                $record_url = BASE_URL.'albums/show/'.$row->id;
            ?>
            <tr>
                <td><?= $row->album_title ?></td>
                <td><?= date('l jS F Y',  strtotime($row->date_released)) ?></td>
                <td><?= $row->label ?></td>
                <td><a href="<?= $record_url ?>"><img src="<?= $picture_path ?>" alt="<?= $row->album_title ?> Cover"></a></td>
                <td><?= anchor('albums/show/'.$row->id, 'View', $attr) ?></td>        
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php 
    if(count($rows)>9) {
        unset($pagination_data['include_showing_statement']);
        echo Pagination::display($pagination_data);
    }
}
?>

<style>
    td {
        vertical-align: top;
    }
    tr td:nth-child(4) {
        text-align: center;
    }
    td img {
        max-width: 100px;
        height:  auto;
    }
</style>