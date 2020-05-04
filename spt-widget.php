<?php
/* * * * * * * * * * * * * * * * * * * * * * * *
Plugin Name: Scripture Page Turner
Plugin URI: http://www.versorbooks.com/
Description: Scripture Page Turner creates a widget to link to the preceding chapter or succeeding chapter for commentaries or translations.
Version: 1.0
Author Name: Nathaniel Turner
Author URI: http://www.versorbooks.com/
License: GPL2
 * * * * * * * * * * * * * * * * * * * * * * * *
*/

class wp_spt_left extends WP_Widget {
	public function __construct() {
		$widget_ops = array('classname' => 'wp_spt_left','description' => 'Links to the preceding chapter for commentaries or translations.');
		parent::__construct('wp_spt_left','Scripture Page Turner (Left)', $widget_ops);
	}
	
	public function form($instance) {
		// do not include a form
	}
	
	public function update($new_instance, $old_instance) {
		$instance = $new_instance;
		return $instance;
	}
	
	public function widget($args, $instance) {
		$curChp = get_the_title(get_the_ID());
		$curChp = explode(' ',$curChp);
		
		$books = books();
		$chapters = chapters();
		
		$curBook = $curChp[0];
		$curBookId = array_search($curBook,$books);
		if ($curBookId === false) {
			$curBook .= ' ' . $curChp[1];
			$curNum = $curChp[2];
			$curBookId = array_search($curBook,$books);
		}
		else { $curNum = $curChp[1]; }
		
		echo $args['before_widget'];
		echo '<div class="widget-text wp_widget_plugin_box spt">';
			$displayLeft = true;
			
			// get Book and Chapter to the left
			if ($curNum == 1) {
				// if this is the first chapter of the first book, don't display a left link
				if ($curBookId == 0) { $displayLeft = false; }
				// at the beginning of a book, display the last chapter of the previous book
				$lBook = $books[$curBookId - 1];
				$lChapter = $chapters[$lBook];
			} else {
				// otherwise, display the previous chapter in the current book
				$lBook = $curBook;
				$lChapter = $curNum - 1;
			}
			
			if ($displayLeft) {
				$displayText = /*'< ' . */$lBook . ' ' . $lChapter;
				$linked = get_post_link($lBook,$lChapter);
				if ($linked !== false) {
					echo '<a href="' . $linked . '" target="_self">' . $displayText . '</a>';
				} else { echo $displayText; }
			}
		echo '</div>';
		echo $args['after_widget'];
	}
}

class wp_spt_right extends WP_Widget {
	public function __construct() {
		$widget_ops = array('classname' => 'wp_spt_right','description' => 'Links to the succeeding chapter for commentaries or translations.');
		parent::__construct('wp_spt_right','Scripture Page Turner (Right)', $widget_ops);
	}
	
	public function form($instance) {
		// no form
	}
	
	public function update($new_instance, $old_instance) {
		$instance = $new_instance;
		return $instance;
	}
	
	public function widget($args, $instance) {
		$curChp = get_the_title(get_the_ID());
		$curChp = explode(' ',$curChp);
		
		$books = books();
		$chapters = chapters();
		
		$curBook = $curChp[0];
		$curBookId = array_search($curBook,$books);
		if ($curBookId === false) {
			$curBook .= ' ' . $curChp[1];
			$curNum = $curChp[2];
			$curBookId = array_search($curBook,$books);
		}
		else { $curNum = $curChp[1]; }
		
		echo $args['before_widget'];
		echo '<div class="widget-text wp_widget_plugin_box spt">';
			$displayRight = true;
			// get Book and Chapter to the right
			if ($curNum == $chapters[$curBook]) {
				// if this is the last chapter of the last book, don't display a right link
				if ($curBookId == count($books) - 1) { $displayRight = false; }
				// at the end of a book, display the first chapter of the next book
				$rBook = $books[$curBookId + 1];
				$rChapter = 1;
			} else {
				// otherwise, display the next chapter of the current book
				$rBook = $curBook;
				$rChapter = $curNum + 1;
			}
			if ($displayRight) {
				$displayText = $rBook . ' ' . $rChapter;// . ' >';
				$linked = get_post_link($rBook,$rChapter);
				if ($linked !== false) {
					echo '<a href="' . $linked . '" target="_self">' . $displayText . '</a>';
				} else { echo $displayText; }
			}
		echo '</div>';
		echo $args['after_widget'];
	}
}

add_action('widgets_init', function(){register_widget('wp_spt_left');});
add_action('widgets_init', function(){register_widget('wp_spt_right');});
add_action('wp_enqueue_scripts','spt_add_my_stylesheet');
add_action('admin_menu','scripture_page_turner');
add_option('spt_version','Protestant');

function spt_add_my_stylesheet() {
	wp_register_style('spt-style',plugins_url('style.css',__FILE__) );
	wp_enqueue_style('spt-style');
}

function scripture_page_turner() {
	add_options_page('Scripture Page Turner Settings',
					 'Scripture Page Turner','manage_options',
					 'pageturner-settings','spt_options');
}

function spt_options() {
	if (!current_user_can('manage_options')) {
	 wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	
	$hidnm = 'spt_hidden';
	$datanm = 'spt_bible_version';
	if (isset($_POST[$hidnm]) && $_POST[$hidnm] == 'Y') {
		//foreach ($datanm as $i => $nm) {
			$opt_val = $_POST[$datanm];
			$opt_name = 'spt_version';
			update_option($opt_name,$opt_val);
		//}
		echo '<div class="updated"><p><strong>';
		_e('Settings saved!','menu-test');
		echo '</strong></p></div>';
	}
	
	$opt_curr = get_option('spt_version','Protestant');
	
	echo '<div class="wrap">';
	echo '<h2>Scripture Page Turner Settings</h2>';
	echo '<form name="form1" method="post" action="">';
	echo '	<table class="form-table">';
	echo '		<tbody>';
	echo '			<tr>';
	echo '				<th scope="row">Which books of the Bible should be included?</th>';
	echo '				<td>';
	echo '					<select name="' . $datanm . '">';
	echo '                      <option ' . ($opt_curr == 'OT' ? 'selected ' : '') . 'value="OT">Old Testament (only)</option>';
	echo '						<option ' . ($opt_curr == 'NT' ? 'selected ' : '') . 'value="NT">New Testament (only)</option>';
	echo '						<option ' . ($opt_curr == 'Protestant' ? 'selected ' : '') . 'value="Protestant">Protestant Bible (66 books, New & Old Testament)</option>';
	echo '						<option ' . ($opt_curr == 'Catholic' ? 'selected ' : '') . 'value="Catholic">Catholic Bible (73 books, New & Old Testament w/ Deuteroncanon)</option>';
	echo '						<option ' . ($opt_curr == 'Eastern' ? 'selected ' : '') . 'value="Eastern">Eastern Orthodox Bible (77 books, New & Old Testament w/ Apocrypha)</option>';
	echo '					</select>';
	echo '				</td>';
	echo '			</tr>';
	echo '		</tbody>';
	echo '	</table>';
	echo '	<input name="' . $hidnm . '" value="Y" type="hidden"/>';
	echo '	<p class="submit">';
	echo '		<input id="submit" class="button button-primary" name="submit" value="Save Changes" type="submit"/>';
	echo '	</p>';
	echo '</form>';
	echo '</div>';
}

function get_post_link($book,$c) {
	global $wpdb;
	$tablename = $wpdb->prefix . 'posts';
	$ref = $book . ' ' . $c;
	$results = $wpdb->get_results('SELECT * FROM ' . $tablename . ' WHERE post_title LIKE "' . $ref . '" AND post_status LIKE "publish"', ARRAY_A );
	if (count($results) == 0) { return false; }
	$slugs = get_option('permalink_structure');
	switch($slugs) {
		case '/%postname%/': $items[0] = 'post_name'; break;
		case '/archives/%post_id%': $items[0] = 'ID'; break;
		case '/%year%/%monthnum%/%postname%/':
		case '/%year%/%monthnum%/%day%/%postname%/': $items[0] = 'post_date'; $items[1] = 'post_name'; break;
		default: $items[0] = 'ID'; $slugs = '/?p=%post_id%'; break;
	}
	if ($items[0] == 'post_date') {
		$date = $results[0][$items[0]];
		$date = explode('-',$date);
		$output[] = $date[0];
		$output[] = $date[1];
		if (strpos($slugs,'day') !== false) {$output[] = $date[2];}
		$output[] = $results[0][$items[1]];
	} else {
		foreach($items as $tag) {
			$output[] = $results[0][$tag];
		}
	}
	foreach($output as $datum) {
		$slugs = preg_replace('/(%\w+%)/',$datum,$slugs);
	}
	$base = get_option('siteurl');
	$link = $base . $slugs;
	return $link;
}

function chapters() {
	$books = books();
	$version = get_option('spt_version');
	if (in_array('Genesis',$books)) 		{ $chapters['Genesis'] = 50; }
	if (in_array('Exodus',$books)) 		{ $chapters['Exodus'] = 40; }
	if (in_array('Leviticus',$books)) 	{ $chapters['Leviticus'] = 27; }
	if (in_array('Numbers',$books)) 	{ $chapters['Numbers'] = 36; }
	if (in_array('Deuteronomy',$books)) { $chapters['Deuteronomy'] = 34; }
	if (in_array('Joshua',$books)) 		{ $chapters['Joshua'] = 24; }
	if (in_array('Judges',$books)) 		{ $chapters['Judges'] = 21; }
	if (in_array('Ruth',$books)) 		{ $chapters['Ruth'] = 4; }
	if (in_array('1 Samuel',$books)) 	{ $chapters['1 Samuel'] = 31; }
	if (in_array('2 Samuel',$books)) 	{ $chapters['2 Samuel'] = 24; }
	if (in_array('1 Kings',$books)) 	{ $chapters['1 Kings'] = 22; }
	if (in_array('2 Kings',$books)) 	{ $chapters['2 Kings'] = 25; }
	if (in_array('1 Chronicles',$books)) { $chapters['1 Chronicles'] = 29; }
	if (in_array('2 Chronicles',$books)) { $chapters['2 Chronicles'] = 36; }
	if (in_array('Ezra',$books)) 		{ $chapters['Ezra'] = 10; }
	if (in_array('Nehemiah',$books)) 	{ $chapters['Nehemiah'] = 13; }
	if (in_array('1 Esdras',$books)) 	{ $chapters['1 Esdras'] = 9; }
	if (in_array('2 Esdras',$books)) 	{ $chapters['2 Esdras'] = 16; }
	if (in_array('Tobit',$books)) 		{ $chapters['Tobit'] = 14; }
	if (in_array('Judith',$books)) 		{ $chapters['Judith'] = 16; }
	if (in_array('Esther',$books)) 		{ $chapters['Esther'] = 10; }
	if (in_array('1 Maccabees',$books)) { $chapters['1 Maccabees'] = 16; }
	if (in_array('2 Maccabees',$books))	{ $chapters['2 Maccabees'] = 15; }
	if (in_array('3 Maccabees',$books)) { $chapters['3 Maccabees'] = 7; }
	if (in_array('Psalm',$books)) 		{ $chapters['Psalm'] = ($version == 'Eastern' ? 151 : 150); }
	if (in_array('Manasses',$books)) 	{ $chapters['Manasses'] = 1; }
	if (in_array('Job',$books)) 		{ $chapters['Job'] = 42; }
	if (in_array('Proverbs',$books)) 	{ $chapters['Proverbs'] = 31; }
	if (in_array('Ecclesiastes',$books)) { $chapters['Ecclesiastes'] = 12; }
	if (in_array('Song of Songs',$books)) { $chapters['Song of Songs'] = 8; }
	if (in_array('Wisdom',$books)) 		{ $chapters['Wisdom'] = 19; }
	if (in_array('Sirach',$books)) 		{ $chapters['Sirach'] = 51; }
	if (in_array('Isaiah',$books)) 		{ $chapters['Isaiah'] = 66; }
	if (in_array('Jeremiah',$books)) 	{ $chapters['Jeremiah'] = 52; }
	if (in_array('Lamentations',$books)) { $chapters['Lamentations'] = 5; }
	if (in_array('Baruch',$books)) 		{ $chapters['Baruch'] = 6; }
	if (in_array('Ezekiel',$books)) 	{ $chapters['Ezekiel'] = 48; }
	if (in_array('Daniel',$books)) 		{ $chapters['Daniel'] = ($version == 'Catholic' || $version == 'Eastern' ? 14 : 12); }
	if (in_array('Hosea',$books)) 		{ $chapters['Hosea'] = 14; }
	if (in_array('Joel',$books)) 		{ $chapters['Joel'] = 3; }
	if (in_array('Amos',$books)) 		{ $chapters['Amos'] = 9; }
	if (in_array('Obadiah',$books)) 	{ $chapters['Obadiah'] = 1; }
	if (in_array('Jonah',$books)) 		{ $chapters['Jonah'] = 4; }
	if (in_array('Micah',$books)) 		{ $chapters['Micah'] = 7; }
	if (in_array('Nahum',$books)) 		{ $chapters['Nahum'] = 3; }
	if (in_array('Habakkuk',$books))	{ $chapters['Habakkuk'] = 3; }
	if (in_array('Zephaniah',$books)) 	{ $chapters['Zephaniah'] = 3; }
	if (in_array('Haggai',$books)) 		{ $chapters['Haggai'] = 2; }
	if (in_array('Zechariah',$books)) 	{ $chapters['Zechariah'] = 14; }
	if (in_array('Malachi',$books)) 	{ $chapters['Malachi'] = 4; }
	if (in_array('4 Maccabees',$books)) { $chapters['4 Maccabees'] = 18; }
	if (in_array('Matthew',$books)) 	{ $chapters['Matthew'] = 28; }
	if (in_array('Mark',$books)) 		{ $chapters['Mark'] = 16; }
	if (in_array('Luke',$books)) 		{ $chapters['Luke'] = 24; }
	if (in_array('John',$books)) 		{ $chapters['John'] = 21; }
	if (in_array('Acts',$books)) 		{ $chapters['Acts'] = 28; }
	if (in_array('Romans',$books)) 		{ $chapters['Romans'] = 16; }
	if (in_array('1 Corinthians',$books)) { $chapters['1 Corinthians'] = 16; }
	if (in_array('2 Corinthians',$books)) { $chapters['2 Corinthians'] = 13; }
	if (in_array('Galatians',$books)) 	{ $chapters['Galatians'] = 6; }
	if (in_array('Ephesians',$books)) 	{ $chapters['Ephesians'] = 6; }
	if (in_array('Philippians',$books))	{ $chapters['Philippians'] = 4; }
	if (in_array('Colossians',$books)) 	{ $chapters['Colossians'] = 4; }
	if (in_array('1 Thessalonians',$books)) { $chapters['1 Thessalonians'] = 5; }
	if (in_array('2 Thessalonians',$books)) { $chapters['2 Thessalonians'] = 3; }
	if (in_array('1 Timothy',$books)) 	{ $chapters['1 Timothy'] = 6; }
	if (in_array('2 Timothy',$books)) 	{ $chapters['2 Timothy'] = 4; }
	if (in_array('Titus',$books)) 		{ $chapters['Titus'] = 3; }
	if (in_array('Philemon',$books)) 	{ $chapters['Philemon'] = 1; }
	if (in_array('Hebrews',$books)) 	{ $chapters['Hebrews'] = 13; }
	if (in_array('James',$books)) 		{ $chapters['James'] = 5; }
	if (in_array('1 Peter',$books)) 	{ $chapters['1 Peter'] = 5; }
	if (in_array('2 Peter',$books)) 	{ $chapters['2 Peter'] = 3; }
	if (in_array('1 John',$books)) 		{ $chapters['1 John'] = 5; }
	if (in_array('2 John',$books)) 		{ $chapters['2 John'] = 1; }
	if (in_array('3 John',$books)) 		{ $chapters['3 John'] = 1; }
	if (in_array('Jude',$books)) 		{ $chapters['Jude'] = 1; }
	if (in_array('Revelation',$books)) 	{ $chapters['Revelation'] = 22; }
	return $chapters;
}
function books() {
	$version = get_option('spt_version');
	$ot = oldtestament();
	$nt = newtestament();
	$cb = catholicbooks();
	$eb = easternbooks();
	switch($version) {
		case 'OT': $a = $ot; break;
		case 'NT': $a = $nt; break;
		case 'Catholic': $a = array_merge($cb,$nt); break;
		case 'Eastern': $a = array_merge($eb,$nt); break;
		default: $a = array_merge($ot,$nt); break;
	}
	return $a;
}
function oldtestament() {
	return array('Genesis','Exodus','Leviticus','Numbers',
				 'Deuteronomy','Joshua','Judges','Ruth',
				 '1 Samuel','2 Samuel','1 Kings','2 Kings',
				 '1 Chronicles','2 Chronicles','Ezra',
				 'Nehemiah','Esther','Job','Psalm',
				 'Proverbs','Ecclesiastes','Song of Songs',
				 'Isaiah','Jeremiah','Lamentations',
				 'Ezekiel','Daniel','Hosea','Joel','Amos',
				 'Obadiah','Jonah','Micah','Nahum','Habakkuk',
				 'Zephaniah','Haggai','Zechariah','Malachi');
}
function newtestament() {
	return array('Matthew','Mark','Luke','John','Acts','Romans',
				 '1 Corinthians','2 Corinthians','Galatians',
				 'Ephesians','Philippians','Colossians',
				 '1 Thessalonians','2 Thessalonians','1 Timothy',
				 '2 Timothy','Titus','Philemon','Hebrews','James',
				 '1 Peter','2 Peter','1 John','2 John','3 John',
				 'Jude','Revelation');
}
function catholicbooks() {
	return array('Genesis','Exodus','Leviticus','Numbers',
				 'Deuteronomy','Joshua','Judges','Ruth',
				 '1 Samuel','2 Samuel','1 Kings','2 Kings',
				 '1 Chronicles','2 Chronicles','Ezra',
				 'Nehemiah','Tobit','Judith','Esther',
				 '1 Maccabees','2 Maccabees','Job','Psalm',
				 'Proverbs','Ecclesiastes','Song of Songs',
				 'Wisdom','Sirach','Isaiah','Jeremiah',
				 'Lamentations','Baruch','Ezekiel','Daniel',
				 'Hosea','Joel','Amos','Obadiah','Jonah',
				 'Micah','Nahum','Habakkuk','Zephaniah',
				 'Haggai','Zechariah','Malachi');
}
function easternbooks() {
	return array('Genesis','Exodus','Leviticus','Numbers',
				 'Deuteronomy','Joshua','Judges','Ruth',
				 '1 Samuel','2 Samuel','1 Kings','2 Kings',
				 '1 Chronicles','2 Chronicles','Nehemiah',
				 '1 Esdras','2 Esdras','Tobit','Judith','Esther',
				 '1 Maccabees','2 Maccabees','3 Maccabees',
				 'Psalm','Manasses','Job','Proverbs','Ecclesiastes',
				 'Song of Songs','Wisdom','Sirach','Isaiah','Jeremiah',
				 'Lamentations','Baruch','Ezekiel','Daniel','Hosea','Joel',
				 'Amos','Obadiah','Jonah','Micah','Nahum','Habakkuk',
				 'Zephaniah','Haggai','Zechariah','Malachi','4 Maccabees');
}