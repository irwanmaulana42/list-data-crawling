<?php
// Create Connection
$servername = "localhost";
$username = "root";
$password = "123456";
$db = "news";
try {
  $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //   echo "Connected successfully";
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

if (@$_GET['search']) {
  $newUrl = str_replace(' ', '%20', $_GET['search']);
  $getData = getData('search', $newUrl);
  $decodedData = json_decode($getData);
  echo "<pre>";
  foreach ($decodedData->data as $value) {
    $cekData = cekData($conn, $value->link);
    if ($cekData == 0) {
      insertData($conn, $value->judul, $value->link, $value->tipe);
      echo "Sukses...";
      echo "<br>";
      echo $value->judul;
      echo "<br>";
    }
  }
  echo "<pre>";
} else if (@$_GET['detail'] == 1) {
  echo "<pre>";
  $getSelectData = selectData($conn);
  if (count($getSelectData) > 0) {
    foreach ($getSelectData as $value) {
      $getData = getData('detail', $value['link']);
      $decodedData = json_decode($getData);
      $getBody = $decodedData->data[0]->body;
      updateData($conn, $getBody, $value['link']);
    }
    echo "Sukses data berhasil di update...";
    echo "<br>";
    echo $value['judul'];
    echo "<br>";
  } else {
    echo "Data kosong.";
  }
  echo "</pre>";
} else {
  echo "Oops...";
}


function getData($query, $search)
{
  $route = '';
  if ($query === 'search') {
    $route = '/search/?q=';
  } else if ($query === 'detail') {
    $route = '/detail/?url=';
  } else {
    die;
  }

  $url = "http://localhost:5000" . $route . $search;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function insertData($conn, $judul, $link, $tipe)
{
  $data = [
    'judul' => $judul,
    'link' => $link,
    'tipe' => $tipe,
  ];
  $sql = "INSERT INTO `data` (judul, link, tipe, created_at) VALUES (:judul, :link, :tipe, NOW())";
  $stmt = $conn->prepare($sql);
  return $stmt->execute($data);
}

function updateData($conn, $body, $link)
{
  $param = [
    'link' => $link,
    'body' => $body
  ];

  $sql = "UPDATE `data` SET detail = :body, updated_at = NOW() WHERE link = :link";
  $stmt = $conn->prepare($sql);
  $stmt->execute($param);
  return true;
}

function cekData($conn, $link)
{
  $param = [
    'link' => $link,
  ];
  $sql = "SELECT count(*) as `count` FROM `data` WHERE link = :link";
  $stmt = $conn->prepare($sql);
  $stmt->execute($param);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  return $data['count'];
}

function selectData($conn)
{
  $data = [];
  $sql = "SELECT * FROM `data` WHERE detail IS NULL";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $data;
}
