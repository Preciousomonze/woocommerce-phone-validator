<?php
/**
 * File to easily zip all files and folders to be ready for plugin install
 * 
 * Also for extraction to wordpress-org folder for auto deploy :)
 * Also try to make your code sniffer ignore this file, e get why :)
 * 
 * @param -ignore_file_path (optional) csv of paths/files to ignore, if not called, there are default paths to be ignored by the script
 * @param -offload (optional) if set, the file will only extract the zip to .wordpress-org folder
 * @author Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
 * @contributors add names here
 */

// Get real path for our folder
$root_path = realpath(__DIR__);
$plugin_name = 'woo-phone-validator';
$folder_path = $plugin_name.'/';

$offload_param = getopt(null, ['offload:']);
$ignore_param = getopt(null, ['ignore_file_path:']);
var_dump($ignore_param);

/**
 * The extractTo() method does not offer any parameter to allow extracting files and folders recursively 
 * from another (parent) folder inside the ZIP archive. 
 * With the following method it is possible:
 * from  stanislav dot eckert at vizson dot de ¶ 
 * https://www.php.net/manual/en/ziparchive.extractto.php
 */
class SubDir_ZipArchive extends ZipArchive{

  public function extractSubdirTo($destination, $subdir){
	$errors = array();

	// Prepare dirs
	$destination = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $destination);
	$subdir = str_replace(array("/", "\\"), "/", $subdir);

	if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR)
	  $destination .= DIRECTORY_SEPARATOR;

	if (substr($subdir, -1) != "/")
	  $subdir .= "/";

	// Extract files
	for ($i = 0; $i < $this->numFiles; $i++) {
	  $filename = $this->getNameIndex($i);

	  if (substr($filename, 0, mb_strlen($subdir, "UTF-8")) == $subdir) {
		$relativePath = substr($filename, mb_strlen($subdir, "UTF-8"));
		$relativePath = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $relativePath);

		if (mb_strlen($relativePath, "UTF-8") > 0) {
		  if (substr($filename, -1) == "/") {  // Directory
			// New dir
			if (!is_dir($destination . $relativePath))
			  if (!@mkdir($destination . $relativePath, 0755, true))
				$errors[$i] = $filename;
		  }
		  else {
			if (dirname($relativePath) != ".") {
			  if (!is_dir($destination . dirname($relativePath))) {
				// New dir (for file)
				@mkdir($destination . dirname($relativePath), 0755, true);
			  }
			}

			// New file
			if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false)
			  $errors[$i] = $filename;
		  }
		}
	  }
	}

	return $errors;
  }
}

#################################################################
// Initialize archive object
$zip = new SubDir_ZipArchive();

##################################################################################################
if ( isset($offload_param['offload']) && $offload_param['offload'] ) {
	if ( $zip->open($plugin_name.'.zip') ) {
		echo "extracting to folder...\n";

		$er =  $zip->extractSubDirTo('.wordpress-org/', $folder_path);

		echo "\n done.";
		echo "\n ok, errors: " . count($er);
	
		$zip->close();
	}
	else{
		echo 'failed to open zip file.';
	}
	exit;
}
###########

// First delete incase theres an old zip file.
unlink($plugin_name.'.zip');
$zip->open($plugin_name.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root_path),
    RecursiveIteratorIterator::LEAVES_ONLY
);

/**
 * Files or paths to ignore zipping, necessary, cause $zip->deleteName() doesnt delete folders for some reason.
 * this is the default, param ignore_file_path overrides if set
 */
$files_to_ignore = array(
	'.git',
	'node_modules',
	'vendor/',
	//'shortcode_text',
);
if ( isset($ignore_param['ignore_file_path']) ) {
	$data_paths = explode(',', $ignore_param['ignore_file_path']);

	if( !empty() ){

	}
}
echo "compressing.....\n";

foreach ( $files as $name => $file ){
    // Skip directories (they would be added automatically)
    if ( !$file->isDir() ) {
		$move_on = false;
        // Get real and relative path for current file
		$file_path = $file->getRealPath();
		$file_path = str_replace('\\', '/', $file_path); // Solved the repeated value of files with back slash :)

		// Ignore Paths
		for ( $i = 0; $i < count($files_to_ignore); $i++ ) {
			if ( strpos($file_path, $files_to_ignore[$i]) !== false ) {
				$move_on = true;
				break;
			}
		}

		if( $move_on == true )
			continue;

		$relative_path = $folder_path.substr($file_path, strlen($root_path) + 1);
		$relative_path = str_replace('\\', '/', $relative_path); // Replace back slash with forward slash,for proper directory
		// Add current file to archive
        $zip->addFile($file_path, $relative_path);
    }
}
// Zip archive will be created only after closing object
$zip->close();
echo "zipped\n";
//now remove some stuff inside the zip file, no better way
if( $zip->open($plugin_name.'.zip') ) {
	$files_to_delete = array(
		'zipper_file.php',
		'README.md',
		//'node_modules/', // Doesn't delete for some weird reason
		'.wordpress-org/',
		'package-lock.json',
		'composer.lock',
		'.eslintrc.json',
		'.distignore',
	);

	for($i = 0; $i < count($files_to_delete); $i++){
		echo "Deleting: ".$files_to_delete[$i]."...\n";
		var_dump( $zip->deleteName($folder_path.$files_to_delete[$i]) ); // Delete this current file too
	}
	$zip->close();
	echo "necessary stuff deleted";
}
else{
	echo 'couldnt delete necessary stuff';
}
