<?php
/**
 * File to easily zip all files and folders to be ready for plugin install
 * 
 * Also for extraction to wordpress-org folder for auto deploy :), don't get?
 * https://github.com/10up/action-wordpress-plugin-deploy for more info.
 * 
 * Make sure you put this file in the same parent directory as your plugin. Will think of doing an update for that later. 
 * Also try to make your code sniffer ignore this file, e get why :)
 * 
 * @param --plugin_name The Name of your plugin, should be the same with your unique slug.
 * @param --ignore_file_path (optional) csv format of paths/files to ignore, if not called, there are default paths to be ignored by the script.
 * @param --delete_files_in_zip (optional) csv format of paths/files to delete in the zip(it also searches for matches, so git will ignore github,etc). this was added cause i felt, adding all to ignore wont be as fast as deleting from the zip file, note: it doesn't delete folders for some reason :(
 * @param --offload (optional) if set, the file will only extract the {plugin_name}.zip file to .wordpress-org folder, any value is true.
 * @param --offload_dir (optional) if not set, defaults extracting to .wordpress-org folder, only useful if -offload param is set.
 * 
 * @author Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
 * @contributors add names here
 */

// Get Params
$plugin_name = getopt(null, ['plugin_name:']);
$ignore_param = getopt(null, ['ignore_file_path:']);
$del_files_in_zip = getopt(null, ['delete_files_in_zip:']);
$offload_param = getopt(null, ['offload:']);
$offload_dir_param = getopt(null, ['offload_dir:']);

// Repackage.
$plugin_name = ( isset($plugin_name['plugin_name']) ? trim($plugin_name['plugin_name']) : null );
$ignore_file_path = ( isset($ignore_param['ignore_file_path']) ? trim($ignore_param['ignore_file_path']) : null );
$del_files_in_zip = ( isset($del_files_in_zip['delete_files_in_zip']) ? trim($del_files_in_zip['delete_files_in_zip']) : null );
$offload_param = ( isset($offload_param['offload']) ? true : null );
$offload_dir_param = ( isset($offload_dir_param['offload_dir']) ? trim($offload_dir_param['offload_dir']) : null );

// Ref note
$ref_note = "Please do not forget to star the repo of this program here: https://github.com/Preciousomonze/wp-plugin-deploy-helper";

## Start work!

if ( empty($plugin_name) ) {
	exit('-plugin_name param required! 😒');
}
//exit;
// Get real path for our folder
$root_path = realpath(__DIR__);
$folder_path = $plugin_name.'/';

#################################################################
// Initialize archive object
$zip = new SubDir_ZipArchive();

##################################################################################################
if ( $offload_param ) {
	if ( $zip->open($plugin_name.'.zip') ) {

		$offload_dir = ( empty($offload_dir_param) ? '.wordpress-org/' : $offload_dir_param );

		// Delete folder and files first.
		echo "deleting folder:[" . $offload_dir. "] ... 🚦🤓\n";
		$del = $zip->del_tree($offload_dir);
		
		echo ( $del == true ? "\nDone deleting.\n" : "\nCould not delete folder.\n" . "\n" );

		echo "extracting to folder:[" . $offload_dir. "] ... 🚦🤓\n";

		$er =  $zip->extract_subdir_to($offload_dir, $folder_path);

		echo "\nDone.";
		echo "\nOk, errors: " . ( count($er) == 0 ? "None! 😁\n" . $ref_note : count($er) . "" );
	
		$zip->close();
	}
	else{
		echo 'OMO! failed to open zip file. ⚠️😢';
	}
	exit;
}
###########

// First delete incase theres an old zip file.
echo "trying to Delete [" .$plugin_name . ".zip] if any... 🚦🤓\n";
if ( unlink($plugin_name.'.zip') ){
	echo "Deleted!\n\n";
}
else{
	echo "Couldn't delete!\n\n";
}

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
);
if ( !empty($ignore_file_path) ) {
	// Update files to ignore.
	$files_to_ignore = explode(',', $ignore_file_path);
}

echo "compressing... 🚦🤓\n";

foreach ( $files as $name => $file ){
    // Skip directories (they would be added automatically)
    if ( !$file->isDir() ) {
		$move_on = false;
        // Get real and relative path for current file
		$file_path = $file->getRealPath();
		$file_path = str_replace('\\', '/', $file_path); // Solved the repeated value of files with back slash :)

		// Ignore Paths
		for ( $i = 0; $i < count($files_to_ignore); $i++ ) {
			$f = trim($files_to_ignore[$i]);
			if ( strpos($file_path, $f) !== false ) {
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
echo "zipped! 😎\n";

if ( !empty($del_files_in_zip) ) {

	// Now remove some stuff inside the zip file, no better way
	if( $zip->open($plugin_name.'.zip') ) {
		$files_to_delete = explode(',', $del_files_in_zip);

		for($i = 0; $i < count($files_to_delete); $i++){
			$file = trim($files_to_delete[$i]);
			if( !empty($file) ){
				echo "Deleting: " . $file . "...\nDeleted:";
				var_dump( $zip->deleteName($folder_path.$file) ); // Delete this current file.
			}
		}
		$zip->close();
		echo "\nNecessary stuff deleted. 😎";
	}
	else{
		echo "OMO! Couldn't delete necessary stuff because file couldn't be opened.  ⚠️😢";
	}
}

echo "\n\nIf your zipping and stuff were successful, congratss!!, else, check around, something must be up, you will surely solve it, Mafo! 💪😊.
 \nIf you are using github action to deploy your WordPress Plugin, do not forget to -offload!
 \nBe like CodeXplorer 🤾🏽‍♂️🥞🦜🤡, and " . $ref_note;

/**
 * The ZipArchive::extractTo() method does not offer any parameter to allow extracting files and folders recursively 
 * from another (parent) folder inside the ZIP archive. 
 * With the following method it is possible
 * 
 * @author stanislav dot eckert at vizson dot de ¶  <https://www.php.net/manual/en/ziparchive.extractto.php>
 * @contributors Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
 */
class SubDir_ZipArchive extends ZipArchive {

	/**
	 * Delete a folder with contents.
	 * 
	 * Glob function doesn't return the hidden files, therefore scandir can be more useful,
	 * when trying to delete recursively a tree.
	 * 
	 * @author  nbari at dalmp dot com ¶ <https://www.php.net/manual/en/function.rmdir.php>
	 * @contributors Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
	 * @return Boolean
	 */
	public function del_tree($dir) {
		if ( !is_dir($dir) ) {
			echo "Folder[" . $dir . "] doesn't exist 🤡.";
			return false;
		}

		$files = array_diff(scandir($dir), array('.','..'));

		 foreach ($files as $file) {
		   ( is_dir("$dir/$file") ) ? $this->del_tree("$dir/$file") : unlink("$dir/$file");
		}

		 return rmdir($dir);
	} 

	/**
	 * Extract to Sub Directory
	 * 
	 * @param string $destination The destination folder
	 * @param string $subdir The sub directory in the zip file
	 * @return mixed
	 */	
	public function extract_subdir_to($destination, $subdir){
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
