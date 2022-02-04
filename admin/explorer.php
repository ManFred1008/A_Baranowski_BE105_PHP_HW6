<?php

if ($_SERVER['PHP_SELF'] == '/admin/explorer.php') {
   header('Location: http://' . $_SERVER['SERVER_NAME'] . '/admin/index.php');
   exit;
}

if($_GET['dir_path'] == './explorer.php' || $_GET['dir_path'] == './index.php') {
   header('Location:./');
   // header('Location: http://' . $_SERVER['SERVER_NAME'] . '/admin/index.php');
   exit;
}

// htmlspecialchars() 
function translit($str) {
   $russian = ['А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' '];

   $trans = ['A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', '_'];

   return str_replace($russian, $trans, $str);
}

function rename_file($path, $name) {
   if(!empty($_POST)) {
      $arr_path = explode('/', $path);         // делаем массив из пути
      array_pop($arr_path);                  // удаляем старое имя файла
      $dir_path = implode('/',$arr_path);  
      $new_path = $dir_path . '/' . $name;

      if ($path == $new_path) exit;

      while (file_exists($new_path)) {       // проверяем наличие файла с таким же именем
         if (is_file($new_path)){
            $name = explode('.', $name);
            $name[0] .= '1';
            $name = implode('.', $name);
            // echo 'Файл с таким именем уже существует, меняем имя файла на' . $name;
            $new_path = $dir_path . '/' . $name;
         } else {
            $name .= '1';
            // echo 'Папка с таким именем уже существует, меняем имя папки на' . $name;
            $new_path = $dir_path . '/' . $name;
         }
      }

      $reg_file_name = '/^[A-Za-z]{1}[\w \.]+\.[a-z]{0,5}$/';
      $reg_dir_name = '/^([\w] ?){1,255}$/';

      $info = pathinfo($new_path);

      if ($info['extension']) {
         $res = preg_match($reg_file_name, $name);
      }
      else {
         $res = preg_match($reg_dir_name, $name);
      }

      if ($res) {
         rename($path, $new_path);
      } else {
         echo "Неверное имя файла!";
      }
   }
}

function remove_file($path) {
   if(is_file($path)) unlink($path);
   else { 
      $path_arr = scandir($path);
      $dir_arr = array_diff($path_arr,['.', '..']);
      foreach($dir_arr as $dir_item) {
         $new_path = $path . '/' . $dir_item;
         if (is_dir($new_path)) remove_file($new_path);
         else unlink($new_path);
      }
   }
   return @rmdir($path);
}

function create_file($path, $name) {

   $name = translit($name);

   $cont_dir = scandir($path);
   while (in_array($name, $cont_dir)) {     // проверяем наличие файла с таким же именем
      $name = explode('.', $name);
      $name[0] .= '1';
      $name = implode('.', $name);
      echo 'Файл с таким именем уже существует, меняем имя файла на' . $name;
   }

   if (!empty((pathinfo($name))['extension'])) {

      $reg_file_name = '/^[A-Za-z]{1}[\w ]+\.[a-z]{0,5}$/';
      if(is_dir($path) && $name && preg_match($reg_file_name, $name)) {
         $file_name = $path . $name;
         $ff = fopen($file_name, 'w+');
         fclose($ff);
         // header('Refresh: 0');
      } else echo 'Введите корректное имя файла';
   } else {
      $reg_dir_name = '/^([\w] ?){1,255}$/';
      if(is_dir($path) && $name && preg_match($reg_dir_name, $name)) {
         $dir_name = $path . $name;
         mkdir($dir_name);
         // header('Refresh: 0');
      } else echo 'Введите корректное имя папки';
   }
}

function edit_file($path, $content) {
   file_put_contents($path, $content);

   $file_dir = explode('/', $path);
   array_pop($file_dir);
   $file_dir = implode('/', $file_dir);
   header('Location:?dir_path=' . $file_dir); 
}

function dir_back($path) {
   $file_dir = explode('/', $path);
   array_pop($file_dir);
   $file_dir = implode('/', $file_dir);
   echo $file_dir;
   header('Location:?dir_path=' . $file_dir); 
}

function upload_files($files) {
   
   $names = $files['name'];
   if(empty($names[0])) exit;
   $dest_path = './uploads';
   if(!file_exists($dest_path)) {
      mkdir($dest_path);
   } 

   foreach($names as $key => $item) {
      $new_name = $item;
      $new_name = translit($new_name);
      while(file_exists($dest_path . '/' . $new_name)) {
         $name_info = pathinfo($new_name);
         $new_name = $name_info['filename'] . '_1.' . $name_info['extension'];
      }
      $move_res = move_uploaded_file($files['tmp_name'][$key], $dest_path . '/' . $new_name);
      if($move_res) $res += 1;
   } 

   if ($res == count($names)) header('Location: ./');
   exit;
}

function get_file_form ($location) {
   if (file_exists($location)) {
      if($location) {
         $class_form = 'form_display';
      } else $class_form = 'form_hidden';

      $form_html = '<div class="'. $class_form .'">';

      $form_html .= '<h3>Редактируем ' . $location . '</h3>';

      $form_html .= '<form action="" " method="post">';

      $file_name = explode('/', $location);
      $file_name = array_pop($file_name);

      $form_html .='
         <input type="text" name="name" value="'. $file_name .'">
         <input type="hidden" name="path" value="'. $location .'">       
         <button type="submit" name="command" value="rename">Rename</button>         
      ';

      $form_html .= '</form>';

      $form_html .= '</div>';

      echo $form_html;
   }
}

function router($path, $command, $name = '') {
   if ((preg_match('/^\.\/.+\.php$/', $path) || preg_match('/^\.\/.+\.ini$/', $path)) && !check_login()) {
      exit;
   }
   switch($command) {

      case 'rename': rename_file($path, $name);
                     header('Refresh: 0');
                     exit;
      break;

      case 'delete': remove_file($path);                     
                     @header('Refresh: 0');
                     exit;
      break;

      case 'edit': if (!empty($name)) edit_file($path, $name);
                     header('Refresh: 0');
                     exit;
      break;

      case 'back': dir_back($path);
                   exit;
      break;

      case 'load': upload_files($_FILES['file']);
                   exit;
      break;

      case 'create': create_file($path, $name);
                     @header('Refresh: 0');
                     exit;
      break;

      default: die();
   }
}

$top_html = '<!DOCTYPE html>
             <html lang="en">
             <head>
               <meta charset="UTF-8">
               <meta http-equiv="X-UA-Compatible" content="IE=edge">
               <meta name="viewport" content="width=device-width, initial-scale=1.0">
               <title>PHP HomeWork 6</title>
               <link rel="stylesheet" href="./style/style.css">
             </head>
             <body>';

$path = './';

if(!empty($_GET['dir_path']) && $_GET['dir_path'] != './') {
   $path = $_GET['dir_path'];
   if (is_dir($path) && $path != '/')  $path .= '/';
}
// echo $path;

// echo '<hr/>'. realpath($path) . '<hr/>';

// echo '<pre>';
// print_r($_SERVER);
// echo '</pre>';
// echo __DIR__;
// echo __FILE__;
if ((preg_match('/^\.\/.+\.php$/', $path) || preg_match('/^\.\/.+\.ini$/', $path)) && !check_login()) {
   header('Location:./');
}

if (is_dir($path)) {
   $dir_arr = scandir($path);

   $list_dir_html = '<ul>';

   if($path != './') $list_dir_html .= '<li><a href="'. './' .'">В рабочий каталог</a></li>'; 

   foreach ($dir_arr as $dir) {

      if ($dir == '.') {
         continue;
      } else $dir_path = $path . $dir;

      $arr_root = explode('\\', __DIR__);
      $root = array_shift($arr_root);

      if($dir == '..') {
         $dir_back = explode('/', $dir_path);
         // echo '<pre>';
         // print_r($dir_back);
         // echo '</pre>';
         if(count($dir_back) > 2) {
            $dir_back = array_slice($dir_back, 0, count($dir_back) - 2);
            $dir_priv = $dir_back[count($dir_back) - 1];
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
      } else if (file_exists($dir_path)) {
         $form_dir = explode('/', $dir_path);
         array_pop($form_dir);
         $form_dir = implode('/', $form_dir);
         if (is_dir($dir_path)) $class_li = 'dir';
         else $class_li = 'file';
         if (is_file($dir_path)) {
            $file_size = filesize($dir_path) . ' байт';
         } else {
            $file_size = '';
         }

         $real_path = realpath($dir_path);
         $index_path = __DIR__ . '\\index.php';
         if(($real_path == __DIR__) || ($real_path == __FILE__) || ($real_path == $index_path)) {
            $list_dir_html .= '
            <li class="'. $class_li .'"> 
               <a href="?dir_path=./">'. $dir .'</a>';
         } else {
            $list_dir_html .= '
            <li class="'. $class_li .'"> 
               <form class="hidden" action="?dir_path='. $form_dir .'" method="post">
               <input type="hidden" name="path" value="'.$dir_path.'"> 
               <button name="form" value="'. $dir_path .'" > Rename '. $dir .'</button>
               <button name="command" value="delete">Delete '. $dir .'</button>
               </form>
               <a href="?dir_path='. $dir_path .'">'. $dir .'</a>';
         }

         $list_dir_html .= '
               <span>'. $file_size .'</span>
               <span>'. date('D, d M Y H:i:s',filectime($dir_path)) .'</span>
               <span>'. $real_path .'</span>
            </li>';
      }
   }

   $list_dir_html .= '</ul>';

   $list_dir_html .= '
   <dir class="forms">
   <form class="form_create" name="create" action="" method="post">
   <input type="text" name="name" value="file">
   <input type="hidden" name="path" value="'.$path.'"> 
   <button name="command" value="create">Create</button>
   </form>';

   $list_dir_html .= '<form class="form_load" enctype="multipart/form-data" action="" method="POST">
                      <input type="file" multiple name="file[]" >
                      <button name="command" value="load">Load</button>
                     ';
   $list_dir_html .= '</form></dir>';

} else {

   $content = file_get_contents($path);

   $list_dir_html = '<form class="form_edit" action="" method="post">
                     <textarea id="#editor" name="name" value="">'. $content .'</textarea>
                     <input type="hidden" name="path" value="'.$path.'"> 
                     <button name="command" value="edit">Save</button>
                     <button name="command" value="back">Back</button>
                     </form>';
}

$bot_html = ' <script src="https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"></script>
               <script>
               ClassicEditor
                     .create( document.querySelector( "#editor" ) )
                     .then( editor => {
                              console.log( editor );
                     } )
                     .catch( error => {
                              console.error( error );
                     } );
               </script>
               <p>jglidfjgldfjglfdklgl;dfkg;ld</p>
             </body>
             </html>';

if (!check_login()) {
   $html_log = '
   <form class="form_create" action="" method="POST">
      <input type="text" name="login" placeholder="login">
      <input type="password" name="pass">
      <button name="sign_in">Sing in</button>
   </form>
   ';
} else {
   $html_log = '
   <form action="" method="POST">
      <button name="exit_sess" value="on">Exit</button>
   </form>
   ';
}



echo $html_log;

echo $top_html;

echo $list_dir_html;

get_file_form($_POST['form']);

router($_POST['path'], $_POST['command'], $_POST['name']);


// print_r(pathinfo('text.php')) ;

echo $bot_html;
?>