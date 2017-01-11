<?php

/* 
 * SubjectToTags Plugin
 *
 * Synchronizes Omeka tags with DC.Subject entries
 *
 */

class SubjectToTagsPlugin extends Omeka_Plugin_AbstractPlugin 
{

  protected $_hooks = array(
  		'after_save_item',
  );
  
  function hookAfterSaveItem($args) 
  {
  	// Add tags based on DC.Subject
  	$litem = $args['record'];
    $tags = metadata($litem, array('Dublin Core', 'Subject'), array('all' => true));
    $itemId = metadata($litem, 'id');
    
  	$db = get_db();
  	$tagsIds = array();    
    foreach ($tags as $id => $tag) {
    	// Create tag if it doesn't exist
    	$tag = str_replace("'", "''", $tag);    	
    	$tagId = $db->query("SELECT id FROM `$db->Tags` WHERE name = '$tag'")->fetch();
    	if (empty($tagId)) {
    		$db->query("INSERT INTO `$db->Tags` (name) VALUES('$tag')");
    		$tagId['id'] = $db->getAdapter()->lastInsertId();    		
    	}
    	$tagsIds[$tag] = $tagId['id'];
    }
    $t = serialize($tagsIds);
    
    // Delete all item tags
    $delete = $db->query("DELETE FROM `$db->RecordsTags` WHERE record_id = $itemId AND record_type='Item'")->fetch();
    // Tag item with choosen tags
    foreach ($tagsIds as $name => $id) {
    	$query = "INSERT INTO `$db->RecordsTags` (record_id, record_type, tag_id) VALUES ($itemId, 'Item', $id)";
			$tagged = $db->query("INSERT INTO `$db->RecordsTags` (record_id, record_type, tag_id) VALUES ($itemId, 'Item', $id)")->fetch();
    }  	  	
  }
}
