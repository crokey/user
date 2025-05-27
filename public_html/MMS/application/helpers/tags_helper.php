<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Function that add and edit tags based on passed arguments
 * @param  string $tags
 * @param  mixed $rel_id
 * @param  string $rel_type
 * @return boolean
 */
function handle_tags_save($tags, $rel_id, $rel_type) 
{
    return _call_tags_method('save', $tags, $rel_id, $rel_type);
}

function handle_tags_new_save($tags, $rel_id, $rel_type, $staff_id, $is_shared) 
{
    return _call_tags_method('newsave', $tags, $rel_id, $rel_type, $staff_id, $is_shared);
}

function handle_tags_save_shared($tags, $rel_id, $rel_type, $is_shared) 
{
    return _call_tags_method('saveshared', $tags, $rel_id, $rel_type, $is_shared);
}

/**
 * Get tag from db by name
 * @param  string $name
 * @return object
 */
function get_tag_by_name($name)
{
    return _call_tags_method('get', $name);
}
/**
 * Function that will return all tags used in the app
 * @return array
 */
function get_tags()
{
    return _call_tags_method('all');
}
/**
 * Array of available tags without the keys
 * @return array
 */
function get_tags_clean()
{
    return _call_tags_method('flat');
}
/**
 * Get all tag ids
 * @return array
 */
function get_tags_ids()
{
    return _call_tags_method('ids');
}
/**
 * Function that will parse all the tags and return array with the names
 * @param  string $rel_id
 * @param  string $rel_type
 * @return array
 */
function get_tags_in($rel_id, $rel_type, $is_shared = 1) {
    return _call_tags_method('relation', $rel_id, $rel_type, $is_shared);
}

function get_tags_in_for_staff($rel_id, $rel_type, $is_shared, $staff_id) {
    $CI =& get_instance();
    $CI->db->select('tags.name');
    $CI->db->from('tags');
    $CI->db->join('taggables', 'tags.id = taggables.tag_id');
    $CI->db->where('taggables.rel_id', $rel_id);
    $CI->db->where('taggables.rel_type', $rel_type);
    $CI->db->where('taggables.is_shared', $is_shared);
    $CI->db->where('taggables.staff_id', $staff_id);

    $query = $CI->db->get();
    $tags = $query->result_array();

    $tag_names = [];
    foreach ($tags as $tag) {
        $tag_names[] = $tag['name'];
    }

    return _call_tags_method('newrelation', $rel_id, $rel_type, $is_shared, $staff_id);
}

function get_tags_in_for_shared($rel_id, $rel_type, $is_shared) {
    $CI =& get_instance();
    $CI->db->select('tags.name');
    $CI->db->from('tags');
    $CI->db->join('taggables', 'tags.id = taggables.tag_id');
    $CI->db->where('taggables.rel_id', $rel_id);
    $CI->db->where('taggables.rel_type', $rel_type);
    $CI->db->where('taggables.is_shared', $is_shared);
    

    

    $query = $CI->db->get();
    $tags = $query->result_array();

    $tag_names = [];
    foreach ($tags as $tag) {
        $tag_names[] = $tag['name'];
    }

    return _call_tags_method('sharedrelation', $rel_id, $rel_type, $is_shared);
}



/**
 * Helper function to call App_tags method
 * @param  string $method method to call
 * @param  mixed $params params
 * @return mixed
 */
function _call_tags_method($method, ...$params)
{
    $CI = &get_instance();

    if (!class_exists('app_tags', false)) {
        $CI->load->library('app_tags');
    }

    return $CI->app_tags->{$method}(...$params);
}

/**
 * Coma separated tags for input
 * @param  array $tag_names
 * @return string
 */
function prep_tags_input($tag_names)
{
    $tag_names = array_filter($tag_names, function ($value) {
        return $value !== '';
    });

    return implode(',', $tag_names);
}


/**
 * Function will render tags as html version to show to the user
 * @param  string $tags
 * @return string
 */
function render_tags($tags)
{
    $tags_html = '';

    if (!is_array($tags)) {
       $tags = empty($tags) ? [] : explode(',', $tags);
    }

    $tags = array_filter($tags, function ($value) {
        return $value !== '';
    });

    if (count($tags) > 0) {
        $CI = &get_instance();

        $tags_html .= '<div class="tags-labels">';
        $i   = 0;
        $len = count($tags);
        foreach ($tags as $tag) {
            $tag_id  = 0;
            $tag_row = $CI->app_object_cache->get('tag-id-by-name-' . $tag);
            if (!$tag_row) {
                $tag_row = get_tag_by_name($tag);

                if ($tag_row) {
                    $CI->app_object_cache->add('tag-id-by-name-' . $tag, $tag_row->id);
                }
            }

            if ($tag_row) {
                $tag_id = is_object($tag_row) ? $tag_row->id : $tag_row;
            }

            $tags_html .= '<span class="label label-tag tag-id-' . $tag_id . '"><span class="tag">' . $tag . '</span><span class="hide">' . ($i != $len - 1 ? ', ' : '') . '</span></span>';
            $i++;
        }
        $tags_html .= '</div>';
    }

    return $tags_html;
}
