<?php 
function dsp_settings_page($search, $basequeryurl='', $search='', $size='', $page='') {
?>
<div class="wrap">
<h3>Select OR Change Your Categories First</h3>

	<form method="post" action="options.php">
    <?php settings_fields( 'dsp-settings-group' ); ?>
    <?php do_settings_sections( 'dsp-settings-group' ); 
	$selected="";?>
	<div id="parent_cat_div" style="float:left">Select Categories <?php wp_dropdown_categories("show_option_none=Select parent category&orderby=name&depth=1&hierarchical=1&id=parent_cat&hide_empty=0"); ?></div>

	<div id="sub_cat_div" style="float:left"><select name="sub_cat_disabled" id="sub_cat_disabled" disabled="disabled"><option>Select parent category first!</option></select></div>

	<div id="sub_child_div" ><select name="sub_child_disabled" id="sub_child_disabled" disabled="disabled"><option>Select Child category first!</option></select></div>
	<h3>Your Videos will be published to these Categories</h3>
	
	<input type="hidden" id="parent_cat_id_hidden" name="parent_cat_id_hidden" value="<?php echo get_option('parent_cat_id_hidden'); ?>">
	Main Category:<input type="text" id="parent_cat_name" value="<?php  echo get_cat_name(get_option('parent_cat_id_hidden')); ?>">
	<input type="hidden" id="child_cat_id_hidden"  name="child_cat_id_hidden" value="<?php echo get_option('child_cat_id_hidden'); ?>">
	Sub Category<input type="text" id="child_cat_name" value="<?php  echo get_cat_name(get_option('child_cat_id_hidden')); ?>">
	<input type="hidden" id="subchild_cat_id_hidden" name="subchild_cat_id_hidden" value="<?php echo get_option('subchild_cat_id_hidden'); ?>">
	Sub Sub Category<input type="text" id="subchild_cat_name" value="<?php  echo get_cat_name(get_option('subchild_cat_id_hidden')); ?>">
	
	<input type="button" class="button-primary" value="Reset" id="reset"/>  
    
	<?php submit_button(); ?>

</form>
</div>
<hr />
<div id="wrapper">
	<h3>DailyMotion Search Videos</h3>

	<form method="POST">
		Search Keyword: <input type="text" name="search" value="<?php echo ( $_POST['search'] ? $_POST['search'] : $_GET['search'] ); ?>">
		Sort: <select name="sort">
		<option name="relevance" value"relevance">relevance</option>
		<option name="recent" value"recent">recent</option>
		</select>
		<input class="button-primary" type="submit" name="submit" value="GO!">
	</form>
	<hr />
</div>
<?php
if ( $_GET['action'] == 'publish' ) {
	$post_title = $_GET['title'];
	$post_entry_id = $_GET['entry_id'];
	$post_duration = $_GET['duration'];
	$post_views = $_GET['views'];
	$post_parent_cat =$_GET['parent_cat'];
	$post_child_cat =$_GET['child_cat'];
	$post_subchild_cat = $_GET['subchild_cat'];
	$post_thumbnail_url = $_GET['thumbnail_url'];
	$video_embed_code = '<iframe frameborder="0" width="600" height="480" src="http://www.dailymotion.com/embed/video/'.esc_html( $post_entry_id ).'?autoplay=1&logo=0&hideInfos=1"></iframe>';
	
// Prepare data array
			$data = array(
			  'post_id' => NULL,
			  'post_title'    => $post_title,
			  'post_content' => $video_embed_code,
			  'post_type'  => 'post',
			  'post_status'   => 'publish',
			  'post_category' => array ($post_parent_cat,$post_child_cat,$post_subchild_cat)			  
			);
			
	$dsp_post_id = wp_insert_post( $data );
	set_post_format($dsp_post_id, 'video' );
	add_post_meta($dsp_post_id, 'tm_video_code', $video_embed_code, true); 
	add_post_meta($dsp_post_id, 'time_video', $post_duration.':00', true); 
	
	$upload_dir = wp_upload_dir();
	$image_data = file_get_contents($post_thumbnail_url);
	$filename = basename($post_thumbnail_url);
	if(wp_mkdir_p($upload_dir['path']))
		$file = $upload_dir['path'] . '/' . $filename;
	else
		$file = $upload_dir['basedir'] . '/' . $filename;
	file_put_contents($file, $image_data);

	$wp_filetype = wp_check_filetype($filename, null );
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => sanitize_file_name($filename),
		'post_content' => '',
		'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $file, $dsp_post_id );
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	set_post_thumbnail( $dsp_post_id, $attach_id );
	
	if( !empty ( $dsp_post_id ) ){
		echo "<div class=\"updated\" style='margin:5px 0 2px;'>";
		echo "	<p><strong>Hurray!!</strong>  <em>" . esc_html( $data['post_title'] ) . "</em> was successfully published. Now you can <strong><a href=\"" . esc_url( admin_url( "post.php?action=edit&post=" . intval( $dsp_post_id) ) ) . "\">Edit</a></strong> it or Publish Next Video.</p>";
		echo "</div><hr />";
	}
}

if ( ( isset ($_POST['search']) || isset ($_GET['search']) ) && $_POST['search'] !== '' ) {
	$search = $_POST['search'] ? $_POST['search'] : $_GET['search'] ;
	$search = str_replace(' ', '+', $search);
	$sort = isset ($_POST['sort'] ) ? $_POST['sort']  : "relevance";
	$size = 10;
	$page = isset( $_GET['p'] ) ? intval( $_GET['p'] ) : 1;
	$from = $size * ( $page - 1 );
	$url="https://api.dailymotion.com/videos?search=".esc_html( $search ).
	"&fields=id,owner,title,url,views_total,owner.screenname,duration,updated_time,thumbnail_180_url,thumbnail_url&language=en&sort=".esc_html( $sort )."&page=".esc_html( $page )."";
	//print_r ($url);

	$json = file_get_contents ( $url, true );

	$content = json_decode($json, true);
	
	echo "<br/><table border='1' width='100%'>";
	echo "<tr>";
	echo "<th>Video</th>";
	echo "<th>Title</th>";
	echo "<th>Author</th>";
	echo "<th>Duration</th>";
	echo "<th>Views</th>";
	echo "<th>Published</th>";
	echo "<th>Action</th>";
	echo "</tr>";
	
	foreach ($content['list'] as $entry ) {
	
	$title = $entry['title'];
	$duration = number_format( $entry['duration']/60 );
	$views = number_format( $entry['views_total'] );
	$url_link = $entry['url'];
	$author = $entry['owner.screenname'];
	$entry_id = $entry['id'];
	$epoch = $entry['updated_time'];
    $dt = new DateTime("@$epoch"); // convert UNIX timestamp to PHP DateTime
    $post_published = $dt->format('d-m-Y H:i:s'); // output = 2012-08-15 00:00:00 
	$results = 100;
	$thumbnail_url = $entry['thumbnail_url'];
	$small_thumb = $entry['thumbnail_180_url'];
	$parent_cat = get_option('parent_cat_id_hidden');
	$child_cat =  get_option('child_cat_id_hidden');
	$subchild_cat =  get_option('subchild_cat_id_hidden');
	$basequeryurl = 'admin.php?page=dsp_settings';
	$url_data = admin_url( $basequeryurl . '&action=publish&entry_id='.esc_html($entry_id).'&title='.esc_html($title).'&duration='.intval($duration).'&views='.intval($views).'&parent_cat='.intval($parent_cat).'&child_cat='.intval($child_cat).'&thumbnail_url='.esc_url( $thumbnail_url ).'&subchild_cat='.intval($subchild_cat) .'&search='.esc_html( $search ).'&p=' . intval( $page ) );

	?>
	<tr>
	<td><img src="<?php echo $small_thumb; ?>" width="200" height="125"></td>
	<td><a target="_blank" href="<?php echo $url_link; ?>" title="<?php echo $title; ?>" rel="nofollow">
	<strong><?php echo $title; ?></strong></a></td>
	<td><?php echo $author; ?></td>
	<td><?php echo  $duration; ?> minutes</td>
	<td><?php echo  $views; ?> views </td>
	<td><?php echo  $post_published; ?> </td>
	<td><a href="<?php echo ( $url_data ); ?>" class="button-primary">Publish Video</a></td>
	</tr>

	<?php
	}
	
	dsp_pagination($results, $page, $size, $basequeryurl, $search);
	echo "</table><hr />";
	dsp_pagination($results, $page, $size, $basequeryurl, $search);

} if ( isset ($_POST['search']) && $_POST['search'] === '') {
	echo "<div class=\"error\" style='text-align:left;margin:5px 0 2px;'><h2>Please type a search keyword</h2></div>";
}

	
}