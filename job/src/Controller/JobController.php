<?php

namespace Drupal\job\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
// use Drupal\media\Entity\Media;
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

    // dump($checkbox);


    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    if(strlen($title)===0 && strlen($location)===0){
        $nids=$node_storage->getQuery()
        ->condition('type', 'job')
        ->execute();
    }else{
        $nids=$node_storage->getQuery()
        ->condition('type', 'job')
        // ->condition('title.value', $title)
        // ->condition('field_location.value', $location)
        ->condition('field_job_type.value', $checkbox)
        ->execute();
    }

        $results = Node::loadMultiple($nids);
         

        

    $jobs = [];
    
    foreach($results as $key => $result){
    //  dump($key);
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
        // dump($result->field_job_type->value);
    };
        
        
        // die;
    return [
      '#theme' => 'job_block',
      '#jobs' => $jobs,
    ];
  }
}