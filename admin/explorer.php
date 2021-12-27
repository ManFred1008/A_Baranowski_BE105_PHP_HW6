<?php

echo '<pre>';

echo '</pre>';

if ($_SERVER['PHP_SELF'] == '/admin/explorer.php') {
   header('Location: http://' . $_SERVER['HTTP_HOST'] . '/admin/index.php');
}

$path = './';

if(!empty($_GET['dir_path']) && $_GET['dir_path'] != './') {
   $path = $_GET['dir_path'];
   if (is_dir($path) && $path != '/')  $path .= '/';
}
echo $path;

if (is_dir($path)) {
   $dir_arr = scandir($path);
}

$list_dir_html = '<ul>';

if($path != './') $list_dir_html .= '<li><a href="'. './' .'">В рабочий каталог</a></li>'; 

foreach ($dir_arr as $dir) {

   if ($dir == '.') {
      continue;
   } else $dir_path = $path . $dir;

   $arr_root = explode('\\', __DIR__);
   // var_dump($arr_root);
   $root = array_shift($arr_root);
   // echo $root;

   if($dir == '..') {
      $dir_back = explode('/', $dir_path);
      print_r($dir_back);
      if(count($dir_back) > 2) {
         $dir_back = array_slice($dir_back, 0, count($dir_back) - 2);
         $dir_priv = $dir_back[count($dir_back)-1];
         if ($dir_priv == '.') $dir_priv = 'рабочий каталог';
         if ($dir_priv == '') {
            $dir_priv = $root;
            $dir_back = array_push($dir_back, '/');
         }
         $dir_back = implode('/', $dir_back);
         $list_dir_html .= '<li> <a href="?dir_path='. (($dir_priv == $root) ? '/' : $dir_back) .'">'. 'Назад в ' . $dir_priv . '</a>'. '' .'</li>';
         $dir = false;
      } else {

         $list_dir_html .= '<li> <a href="?dir_path='. '/' .'">' . 'К корню диска '.$root.'' . '</a></li>';
         $dir = '/';
      }
   } else if (is_dir($dir_path)) {
      // $dir_name = '[ ' . $dir . ' ]';
      $list_dir_html .= '
         <li class="dir"> 
            <a href="?dir_path='. $dir_path .'">'. $dir .'</a>
            <span>'. date('D, d M Y H:i:s',filectime($dir_path)) .'</span>
         </li>';
   } else $list_dir_html .= '
            <li class="file"> 
               <form class="hidden" action="./"><button type="submit" name="form" value="'. $dir_path .'" > Редактировать '. $dir .'</button></form> 
               <a href="?dir_path='. $dir_path .'">'. $dir .'</a> 
               <span>'. date( 'D, d M Y H:i:s',filectime($dir_path)).'</span>
            </li>';
}

$list_dir_html .= '</ul>';

echo $list_dir_html;

// echo $_GET['form'];


function get_file_form ($location) {
   if (is_file($location)) {
      if($location) {
         $class_form = 'form_display';
      } else $class_form = 'form_hidden';
      
      $form_html = '<form class="'. $class_form .'" name="set" action="/'. /*$location $_SERVER['PHP_SELF'] .*/'" method="post">';

      $form_html .= '<h3>Редактируем файл' . $location . '</h3>';

      $form_html .='
         <input type="text" name="name" value="File name">
         <input type="hidden" name="path" value="'.$location.'"> 
         <button type="submit" name="command" value="save">Save</button>         
         <button type="submit" name="command" value="rename">Rename</button>         
         <button type="submit" name="command" value="delete">Delete</button>
         <button type="submit" name="command" value="edit">Edit</button>
      ';

      $form_html .= '</form>';

      echo $form_html;
   }
}

function router($path, $command, $name) {
   if ($path == './explorer.php' || $path == './index.php') {
      echo 'Файл нельзя редактировать!!!';
      continue;
   } else {
      switch($command) {

         case 'rename': if (!empty($name)) rename_file($path, $name);
         break;

         case 'save': if (!empty($name)) save_file($path, $name);
         break;

         case 'delete': if (!empty($name)) delete_file($path, $name);
         break;

         case 'edit': if (!empty($name)) edit_file($path, $name);
         break;
      }
   }
   
}

function rename_file($path, $name) {
   if(!empty($_POST)) {
      $arr_path = explode('/', $path);         // делаем массив из пути
      array_pop($arr_path);                    // удаляем старое имя файла
      $reg_name = '/^[A-Za-z]{1}[\w ]+\.[a-z]{0,5}$/';
      if (preg_match($reg_name, $name)) {
         array_push($arr_path, $name);           // добавляем новое имя файла
         $new_name = implode('/', ($arr_path));  // превращаем массив в путь
         rename($path, $new_name);
         echo 'Rename_OK!!';
      } else {
         echo "Неверное имя файла!";
      }
   }
}

// function remove_file()

get_file_form($_GET['form']);

router($_POST['path'], $_POST['command'], $_POST['name']);
// var_dump($_REQUEST);

// rename_file($_POST['path'], $_POST['name']);
?>