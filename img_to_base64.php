<?php
$file = '/index.html';
$css_file = '/style.css';
$dir_structure = array('beeline','tele2');
$img_encod = array();
$directories = array();
$directory = "/home/astaroth/server/land/".$_POST['directory'];

$sans = file_get_contents('opensans.txt');

function change_rules($foldername){
	if(chmod($foldername, 0777)){
		echo "change of rights to file \"".$foldername."\"completed<br>";
	} else{
		echo "change of rights to file \"".$foldername."\"is not completed<br>";
	}
}

function create_folder($foldername){
	if(!is_writable($foldername)){
		if(mkdir($foldername, 0777)){
			echo "directory \"".$foldername."\" created<br>";
		change_rules($foldername);
		}else{
			echo "directory \"".$foldername."\" not created!";
			exit;
		}
	} else{
		echo "directory \"".$foldername."\" exist!<br>";
	}
}

if(is_writable($directory)){
	$get_file_dir = array_diff(scandir($directory), array('..', '.'));
	$get_directories = array_intersect ($dir_structure, $get_file_dir);
	$encode_directory = $directory."/base64";
	create_folder($encode_directory);
} else{
	echo "directory \"".$directory."\" not writable!";
	exit;
}
echo "<br>";
foreach($get_directories as $value){
	$encode_folder = $encode_directory."/".$value;
	$encode_file = $encode_folder.$file;
	$original_folder = $directory."/".$value;
	$original_file = $original_folder.$file;
	$original_css_file = $original_folder.$css_file;
	
	if(is_readable($original_file)){
		echo "\"".$original_file."\" readable<br>";
		create_folder($encode_folder);
		$string = file_get_contents($original_file);
	}else{
		echo "\"".$original_file."not found or unavailable for reading!!!";
		exit;
	}
	
	//получение css строки из файла и вставка css в html код
	if(is_readable($original_css_file)){
		echo "\"".$original_css_file."\" readable<br>";
		$css_code = '<style type="text/css">'."\n".$sans."\n".file_get_contents($original_css_file).'</style>';
		$string = str_replace('<link rel="stylesheet" href="style.css" />', $css_code, $string);
	} else {
		echo "\"".$original_css_file."\"file not found or unavailable for reading<br>";
	}
	
	// поиск пути всех изображений
	preg_match_all('#\"(img\/[^\"]+)\"#is',$string, $image_file);
	foreach($image_file[1] as $te){
		$files = $original_folder."/".$te;// подстановка пути для чтения изображения 
		$img = base64_encode(file_get_contents($files));// кодировка изображений в base64
		$tv = "data:".mime_content_type($files).";base64,".$img;// подготовка к замене пути изображения на base64
		$img_encod[$te] = $tv; // наполнение массива "путь к файлу" => "base64"
	}	
	// замена пути на base64
	$encode_content = str_replace(array_keys($img_encod), array_values($img_encod), $string);

	// сохранение замененного html кода с изменением прав
	if(file_put_contents($encode_file, $encode_content) !== FALSE){
		echo "write to file \"".$encode_file."\" completed<br>";
		change_rules($encode_file);
	} else{
		echo "write to file \"".$encode_file."\" failed<br><br>";
	}
	echo "<br>";
}
