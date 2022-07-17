<?php
namespace Drupal\shopify_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides route responses for the Example module.
 */
class ProductController extends ControllerBase {

  private $base_url = 'https://1254-56.myshopify.com/admin/api/2022-07';
  private $access_token = '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';
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function list() {
    $query = \Drupal::database();
    // $results = $query->select('products','e')
    //           ->fields('e',['id','title','price'])
    //           ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')->limit(10)
    //           ->execute()->fetchAll(\PDO::FETCH_OBJ);
    $client = new Client(['headers' => ['X-Shopify-Access-Token' => $this->access_token]]);
    $response = $client->get($this->base_url.'/products.json');
    $results = json_decode($response->getBody(), TRUE);

    $rows = [];
    foreach($results["products"] as $result){
      $price = '';
      if (count($result["variants"]) == 1){
        $price = '<p>'.$result["variants"][0]["price"].'</p>';
      }
      else{
        foreach($result["variants"] as $variant){
          $price .= '<p>'.$variant["title"].' '.$variant["price"].'</p>';
        }
      }

      if($result['image'] !=null){
        $image = '<img src="'.$result['image']['src'].'" alt="photo" style="width:60px;height:100px">';
      }else{
        $image = '<p>Image Not Available</p>';
      }
      $rows[] = [
        'title' => $result["title"],
        'price' => Markup::create($price),
        'image' => Markup::create($image),
        'action' => t('<a href="product-details/'.$result["id"].'">Details</a>')
      ];
    }

    $header = [
      'title' => t('Title'),
      'price' => t('Price'),
      'image' => t('Image'),
      'action' => t('Action'),
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No product has been found.'),
    ];

    $build['pager'] = [
      '#type' => 'pager'
    ];

    return [
      '#type' => '#markup',
      '#markup' => render($build)
    ];
  }

  public function details(){
    $id = \Drupal::routeMatch()->getParameter('id');
    $client = new Client(['headers' => ['X-Shopify-Access-Token' => $this->access_token]]);
    $response = $client->get($this->base_url.'/products/'.$id.'.json');
    $result = json_decode($response->getBody(), TRUE);

    $render = '<h1>'.$result['product']['title'].'</h1>'.
              '<p>'.$result['product']['body_html'].'</p>';
    if (count($result['product']["variants"]) == 1){
      $render .= '<b>'.$result['product']["variants"][0]["price"].'</b><br>';
      if ($result['product']['image'] !=null)
      $render .= '<img src="'.$result['product']['image']['src'].'" alt="photo" style="width:150px;height:200px">';
    }
    else{
      $render .= '<h2>Variants</h2>';
      foreach ($result['product']["variants"] as $variant){
        $render .= '<b>'.$variant["title"].'</b><br><br>'.  '<b>$'.$variant["price"].'</b><br>';
        if ($result['product']["images"] !=null){
          foreach ($result['product']["images"] as $variantImage){
            if ($variant["image_id"] == $variantImage['id'])
              $render .= '<img src="'.$variantImage['src'].'" alt="photo" style="width:150px;height:200px">';
          }
        }

        $render .= '<br><br>';
      }
    }

    $render =Markup::create($render);
    return [
      '#type' => '#markup',
      '#markup' => render($render)
    ];
  }

}
