<?php
class Albums extends Trongate {

    private $default_limit = 20;
    private $per_page_options = array(10, 20, 50, 100);    

    function create() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $update_id = segment(3);
        $submit = post('submit');

        if (($submit == '') && (is_numeric($update_id))) {
            $data = $this->_get_data_from_db($update_id);
        } else {
            $data = $this->_get_data_from_post();
        }

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update Album Record';
            $data['cancel_url'] = BASE_URL.'albums/show/'.$update_id;
        } else {
            $data['headline'] = 'Create New Album Record';
            $data['cancel_url'] = BASE_URL.'albums/manage';
        }

        $data['form_location'] = BASE_URL.'albums/submit/'.$update_id;
        $data['view_file'] = 'create';
        $this->template('admin', $data);
    }

    function manage() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';
            $searchphrase = trim($_GET['searchphrase']);
            $params['album_title'] = '%'.$searchphrase.'%';
            $params['label'] = '%'.$searchphrase.'%';
            $sql = 'select * from albums
            WHERE album_title LIKE :album_title
            OR label LIKE :label
            ORDER BY date_released';
            $all_rows = $this->model->query_bind($sql, $params, 'object');
        } else {
            $data['headline'] = 'Manage Albums';
            $all_rows = $this->model->get('date_released');
        }

        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = 'albums/manage';
        $pagination_data['record_name_plural'] = 'albums';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = 'albums';
        $data['view_file'] = 'manage';
        $this->template('admin', $data);
    }

    function show() {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = segment(3);

        if ((!is_numeric($update_id)) && ($update_id != '')) {
            redirect('albums/manage');
        }

        $data = $this->_get_data_from_db($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect('albums/manage');
        } else {
            //generate picture folders, if required
            $picture_settings = $this->_init_picture_settings();

            $this->_make_sure_got_destination_folders($update_id, $picture_settings);

            //attempt to get the current picture
            $column_name = $picture_settings['target_column_name'];

            if ($data[$column_name] !== '') {
                //we have a picture - display picture preview
                $data['draw_picture_uploader'] = false;
            } else {
                //no picture - draw upload form
                $data['draw_picture_uploader'] = true;
            }

            $data['update_id'] = $update_id;
            $data['headline'] = 'Album Information';
            $data['view_file'] = 'show';
            $this->template('admin', $data);
        }
    }
    
    function _reduce_rows($all_rows) {
        $rows = [];
        $start_index = $this->_get_offset();
        $limit = $this->_get_limit();
        $end_index = $start_index + $limit;

        $count = -1;
        foreach ($all_rows as $row) {
            $count++;
            if (($count>=$start_index) && ($count<$end_index)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    function submit() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit', true);

        if ($submit == 'Submit') {

            $this->validation_helper->set_rules('album_title', 'Album Title', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('date_released', 'Date Released', 'required|valid_datepicker_us');
            $this->validation_helper->set_rules('label', 'Label', 'required|min_length[2]|max_length[255]');

            $result = $this->validation_helper->run();

            if ($result == true) {

                $update_id = segment(3);
                $data = $this->_get_data_from_post();
                $data['date_released'] = date('Y-m-d', strtotime($data['date_released']));

                if (is_numeric($update_id)) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'albums');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $update_id = $this->model->insert($data, 'albums');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('albums/show/'.$update_id);

            } else {
                //form submission error
                $this->create();
            }

        }

    }

    function submit_delete() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit');
        $params['update_id'] = segment(3);

        if (($submit == 'Yes - Delete Now') && (is_numeric($params['update_id']))) {
            //delete all of the comments associated with this record
            $sql = 'delete from trongate_comments where target_table = :module and update_id = :update_id';
            $params['module'] = 'albums';
            $this->model->query_bind($sql, $params);

            //delete the record
            $this->model->delete($params['update_id'], 'albums');

            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('albums/manage');
        }
    }

    function _get_limit() {
        if (isset($_SESSION['selected_per_page'])) {
            $limit = $this->per_page_options[$_SESSION['selected_per_page']];
        } else {
            $limit = $this->default_limit;
        }

        return $limit;
    }

    function _get_offset() {
        $page_num = segment(3);

        if (!is_numeric($page_num)) {
            $page_num = 0;
        }

        if ($page_num>1) {
            $offset = ($page_num-1)*$this->_get_limit();
        } else {
            $offset = 0;
        }

        return $offset;
    }

    function _get_selected_per_page() {
        if (!isset($_SESSION['selected_per_page'])) {
            $selected_per_page = $this->per_page_options[1];
        } else {
            $selected_per_page = $_SESSION['selected_per_page'];
        }

        return $selected_per_page;
    }

    function set_per_page($selected_index) {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (!is_numeric($selected_index)) {
            $selected_index = $this->per_page_options[1];
        }

        $_SESSION['selected_per_page'] = $selected_index;
        redirect('albums/manage');
    }

    function _get_data_from_db($update_id) {
        $record_obj = $this->model->get_where($update_id, 'albums');

        if ($record_obj == false) {
            $this->template('error_404');
            die();
        } else {
            $data = (array) $record_obj;
            return $data;        
        }
    }

    function _get_data_from_post() {
        $data['album_title'] = post('album_title', true);
        $data['date_released'] = post('date_released', true);
        $data['label'] = post('label', true);        
        return $data;
    }

    function _init_picture_settings() { 
        $picture_settings['max_file_size'] = 2000;
        $picture_settings['max_width'] = 1200;
        $picture_settings['max_height'] = 1200;
        $picture_settings['resized_max_width'] = 450;
        $picture_settings['resized_max_height'] = 450;
        $picture_settings['destination'] = 'albums_pics';
        $picture_settings['target_column_name'] = 'picture';
        $picture_settings['thumbnail_dir'] = 'albums_pics_thumbnails';
        $picture_settings['thumbnail_max_width'] = 120;
        $picture_settings['thumbnail_max_height'] = 120;
        return $picture_settings;
    }

    function _make_sure_got_destination_folders($update_id, $picture_settings) {
        $destination = $picture_settings['destination'];
        $destination = 'modules/'.segment(1).'/assets/images/'.$destination;

        $target_dir = APPPATH.$destination.'/'.$update_id;

        if (!file_exists($target_dir)) {
            //generate the image folder
            mkdir($target_dir, 0777, true);
        }

        //attempt to create thumbnail directory
        $thumbnail_dir = trim($picture_settings['thumbnail_dir']);
        $thumbnail_dir = 'modules/'.segment(1).'/assets/images/'.$thumbnail_dir;

        if (strlen($thumbnail_dir)>0) {
            $target_dir = APPPATH.$thumbnail_dir.'/'.$update_id;
            if (!file_exists($target_dir)) {
                //generate the image folder
                mkdir($target_dir, 0777, true);
            }
        }
    }

    function submit_upload_picture($update_id) {

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if ($_FILES['picture']['name'] == '') {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $submit = post('submit');

        if ($submit == 'Upload') {
            $picture_settings = $this->_init_picture_settings();
            extract($picture_settings);

            $validation_str = 'allowed_types[gif,jpg,jpeg,png]|max_size['.$max_file_size.']|max_width['.$max_width.']|max_height['.$max_height.']';
            $this->validation_helper->set_rules('picture', 'item picture', $validation_str);

            $result = $this->validation_helper->run();

            if ($result == true) {

                $config['destination'] = $destination.'/'.$update_id;
                $config['max_width'] = $max_width;
                $config['max_height'] = $max_height;

                if ($thumbnail_dir !== '') {
                    $config['thumbnail_dir'] = $thumbnail_dir.'/'.$update_id;
                    $config['thumbnail_max_width'] = $thumbnail_max_width;
                    $config['thumbnail_max_height'] = $thumbnail_max_height;
                }

                //upload the picture
                $this->upload_picture_alt($config);

                //update the database
                $data[$target_column_name] = $_FILES['picture']['name'];
                $this->model->update($update_id, $data);

                $flash_msg = 'The picture was successfully uploaded';
                set_flashdata($flash_msg);
                redirect($_SERVER['HTTP_REFERER']);

            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

    }

    public function upload_picture_alt($data) {
        //check for valid image width and mime type
        $userfile = array_keys($_FILES)[0];
        $target_file = $_FILES[$userfile];

        $dimension_data = getimagesize($target_file['tmp_name']);
        $image_width = $dimension_data[0];

        if (!is_numeric($image_width)) {
            die('ERROR: non numeric image width');
        }

        $content_type = mime_content_type($target_file['tmp_name']);

        $str = substr($content_type, 0, 6);
        if ($str !== 'image/') {
            die('ERROR: not an image.');
        }

        $tmp_name = $target_file['tmp_name'];
        $data['image'] = new Image($tmp_name);

        $dir_path = 'modules/'.segment(1).'/assets/images/';
        $data['destination'] = $dir_path.$data['destination'];
        $data['thumbnail_dir'] = $dir_path.$data['thumbnail_dir'];

        $data['filename'] = '../'.$data['destination'].'/'.$target_file['name'];

        $data['tmp_file_width'] = $data['image']->getWidth();
        $data['tmp_file_height'] = $data['image']->getHeight();

        if (!isset($data['max_width'])) {
            $data['max_width'] = NULL;
        }

        if (!isset($data['max_height'])) {
            $data['max_height'] = NULL;
        }

        $this->save_that_pic_alt($data);
       
        //rock the thumbnail
        if ((isset($data['thumbnail_max_width'])) && (isset($data['thumbnail_max_height'])) && (isset($data['thumbnail_dir']))) {
            $data['filename'] = '../'.$data['thumbnail_dir'].'/'.$target_file['name'];
            $data['max_width'] = $data['thumbnail_max_width'];
            $data['max_height'] = $data['thumbnail_max_height'];
            $this->save_that_pic_alt($data);
        }
    }

    private function save_that_pic_alt($data) {
        extract($data);
        $reduce_width = false;
        $reduce_height = false;

        if (!isset($data['compression'])) {
            $compression = 100;
        } else {
            $compression = $data['compression'];
        }

        if (!isset($data['permissions'])) {
            $permissions = 775;
        } else {
            $permissions = $data['permissions'];
        }

        //do we need to resize the picture?
        if ((isset($max_width)) && ($tmp_file_width>$max_width)) {
            $reduce_width = true;
        }

        if ((isset($max_height)) && ($tmp_file_width>$max_height)) {
            $reduce_height = true;
        }

        //resize rules figured out, let's rock...
        if (($reduce_width == true) && ($reduce_height == false)) {
            $image->resizeToWidth($max_width);
            $image->save($filename, $compression);
        }

        if (($reduce_width == false) && ($reduce_height == true)) {
            $image->resizeToHeight($max_height);
            $image->save($filename, $compression);
        }

        if (($reduce_width == false) && ($reduce_height == false)) {
            $image->save($filename, $compression);
        }

        if (($reduce_width == true) && ($reduce_height == true)) {
            $image->resizeToWidth($max_width);
            $image->resizeToHeight($max_height);
            $image->save($filename, $compression);
        }
    }

    function ditch_picture($update_id) {

        if (!is_numeric($update_id)) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $result = $this->model->get_where($update_id);

        if ($result == false) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $picture_settings = $this->_init_picture_settings();
        $target_column_name = $picture_settings['target_column_name'];
        $picture_name = $result->$target_column_name;
        $picture_path = $picture_settings['destination'].'/'.$update_id.'/'.$picture_name;

        if (file_exists($picture_path)) {
            unlink($picture_path);
        }

        if (isset($picture_settings['thumbnail_dir'])) {
            $thumbnail_path = $picture_settings['thumbnail_dir'].'/'.$update_id.'/'.$picture_name;
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }
        }

        $data[$target_column_name] = '';
        $this->model->update($update_id, $data);
        
        $flash_msg = 'The picture was successfully deleted';
        set_flashdata($flash_msg);
        redirect($_SERVER['HTTP_REFERER']);
    }
}