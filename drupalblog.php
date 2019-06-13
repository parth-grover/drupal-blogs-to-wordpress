<?php
include('../../../inc/global.inc.php');
function blog_title():array
{
	$sql = "SELECT * FROM `drupal`.`node` WHERE `type` = 'blog'";
    $res = ABDatabase::run_sql_query($sql);
    $userdata = array();
    if (ABDatabase::num_rows($res) > 0)
    {
        while($result = ABDatabase::fetch_array($res)){
            $userdata[] = $result;      
        }
    }
    return $userdata;
	
}

function blog_body(...$nid):array
{
	$sql = "SELECT * FROM `drupal`.`field_data_body` WHERE `entity_id` = '".$nid[0]."'";
    $res = ABDatabase::run_sql_query($sql);
    if(ABDatabase::num_rows($res) > 0){
        $bb_details = ABDatabase::fetch_array($res);
        return $bb_details;
    } 
	
}


function blog_image(...$nid)
{
	$sql ="SELECT * FROM `drupal`.`file_managed` fm INNER join `drupal`.`field_data_field_index_image` fi on fi.field_index_image_fid = fm.fid where `entity_id` = '".$nid[0]."'";
	$res = ABDatabase::run_sql_query($sql);
    if(ABDatabase::num_rows($res) > 0){
        $bi_details = ABDatabase::fetch_array($res);
        return $bi_details;
    } 
}

function blog_comment(...$nid)
{
	$sql ="SELECT * from `drupal`.`comment` WHERE `nid` = '".$nid[0]."'";
	$res = ABDatabase::run_sql_query($sql);
   $userdata = array();
    if (ABDatabase::num_rows($res) > 0)
    {
        while($result = ABDatabase::fetch_array($res)){
            $userdata[] = $result;      
        }
    }
    return $userdata;
}

$blogs = blog_title();


foreach($blogs as $blog):
	
	$nid = $blog["nid"];
	echo $nid.'<br>';
	$body = blog_body($nid);
	$image = blog_image($nid);
	$comments = blog_comment($nid);
	
	$post_title = $blog["title"];
	$post_title = str_replace("'", "", $post_title);
	$post_name = preg_replace("/[^a-zA-Z]+/", "", $post_title);
	$post_date = date('Y-m-d H:i:s', $blog['created']);
	$post_body = $body["body_value"];
	$body_post = str_replace("'", "", $post_body);
	
	
	if(!empty($image)){
	$array = explode('/', $image["uri"],3);
	$blog_image_path = $array[2];
	$blog_image_path = explode('.', $blog_image_path,2);
	$blog_image_path = $blog_image_path[0];
	}else{
	$blog_image_path = "";
	}
		
	$post_insert_sql = "insert into `wordpress`.`wp_posts`(`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_status`,`comment_status`,`ping_status`,`post_name`,`post_type`) values('".$post_date."','".$post_date."','".$body_post."','".$post_title."','publish','open','open','".$post_name."','post')";
	$post_insert_query = ABDatabase::run_sql_query($post_insert_sql);
	if($post_insert_query)
				$post_insert_id = ABDatabase::get_last_insert_id();
					echo $post_insert_id.'<br>';
			
	$image_sql = "SELECT * from `wordpress`.`wp_posts` where post_title ='".$blog_image_path."'";
	$res = ABDatabase::run_sql_query($image_sql);
	$md_details = ABDatabase::fetch_array($res);
	$image_id = $md_details["ID"];
	echo $image_id.'<br>';
	
	if(!empty($image_id)){
	$image_thumb_sql = "insert into `wordpress`.`wp_postmeta`(`post_id`,`meta_key`,`meta_value`) values('".$post_insert_id."','_thumbnail_id','".$image_id."')";
	$image_thumb_query = ABDatabase::run_sql_query($image_thumb_sql);
	echo $image_thumb_sql.'<br>'; 
	}
	
	
	foreach($comments as $comment){
		$comment_author_name = $comment["name"];
		$comment_subject = $comment["subject"];
		$comment_date = date('Y/m/d H:i:s', $comment['created']);
		$comment_email = $comment["mail"];
		
		$comment_insert_sql = "insert into `wordpress`.`wp_comments`(`comment_post_ID`,`comment_author`,`comment_author_email`,`comment_date`,`comment_date_gmt`,`comment_content`,`comment_approved`) values('".$post_insert_id."','".$comment_author_name."','".$comment_email."','".$comment_date."','".$comment_date."','".$comment_subject."','1')";
		$comment_insert_query = ABDatabase::run_sql_query($comment_insert_sql);
		echo $comment_insert_sql.'<br>';
		
	}
	
	
endforeach;


?>