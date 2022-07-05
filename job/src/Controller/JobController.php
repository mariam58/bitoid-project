<?php

namespace Drupal\job\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * An example controller.
 */
class JobController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  
   
  public function content() {

    $title = \Drupal::request()->request->get('title');
    $location = \Drupal::request()->request->get('location');
    $checkbox = \Drupal::request()->request->get('full_time');
    $conditions_array= array();

    if(! empty($title)){
      $conditions_array[] = ['title.value', $title];
    }
    if(! empty($location)){
      $conditions_array[] = ['field_location.value', $location];
    }
    if(count($conditions_array) > 0){
      $query = \Drupal::entityQuery('node');
      $group = $query->orConditionGroup()
        ->condition('title.value', $title)
        ->condition('field_location.value', $location);
      $nids = $query
        ->condition('type', 'job')
        ->condition($group)
        ->execute();
    }else{
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nids=$node_storage->getQuery()
      ->condition('type', 'job')
      ->execute();
    }
    
    $results = Node::loadMultiple($nids);

    $jobs = [];
    
    foreach($results as $key => $result){
      $node_url = array_keys($results);
        $fid = $result->field_thumbnail->getValue()[0]['target_id'];
            $file = File::load($fid);
            $image_uri = $file->getFileUri();
            $style = ImageStyle::load('thumbnail');
            $uri = $style->buildUri($image_uri);
            $url = $style->buildUrl($image_uri);
      
        $jobs[]=[
          'url_to_node' => $key,
          'thumbnail' => $url,
          'date' => $result->field_job_date->value,                 
          'job_type' => $result->field_job_type->value,          
          'title' => $result->title->value,
          'company_name' => $result->field_com->value,          
          'location' => $result->field_location->value,          
        ];
    };

    return [
      '#theme' => 'job_block',
      '#jobs' => $jobs,
    ];
  }
}