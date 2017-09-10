<!DOCTYPE html>
<html>
<head>
    <title>C code</title>
</head>
<body>
<?php
/*Reading File Starts*/
  $target_dir = "";
  $target_file = $target_dir .$_FILES["fileToUpload"]["name"];
  move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
  echo "File Uploaded successfully<hr>";
/*Reading File Ends*/

/*Formatting Code Starts*/
  $source = fopen("code.c", "r+") or die("Unable to open file!");
  $formatted = fopen("code-formatted.c", "w+") or die("Unable to open file!");
  $collector="";
  function checkBrace (&$line)
    {
      if(strpos($line, "for")!==false && strpos($line, "for")!==0) {$line=substr_replace($line,"\n"."for ",strpos($line,"for"), 1);}

      if(strpos($line, "{")!==false && strpos($line, "{")!==0) {$line=substr_replace($line,"\n"."{ ",strpos($line,"{"), 1);}

      if(strrpos($line, "}")===(strlen($line)-1)) {$line=substr_replace($line,"\n "."}",strpos($line,"}"), 1);}
    }

  while (!feof($source))
   {
      $line=trim(fgets($source));
      checkBrace ($line);
      $collector=$collector.$line."\n";
   }

  $token = trim(strtok($collector, "\n"));
  while ($token !==false)
  {
     if(trim($token) !==""){fwrite($formatted,"\n".$token);}
    $token = strtok("\n");
  }
  fclose($source);
  fclose($formatted);
/*Formatting Code Ended*/

/*Showing For Line Number & Content*/
  $source = fopen("code.c", "r+") or die("Unable to open file! ");
  $formatted = fopen("code-formatted.c", "r+") or die("Unable to open file!");
  $SourceCounter=0;
  echo '<form action="2.php" method="post" enctype="multipart/form-data">';
  while (!feof($source))
    {
     $line=trim(fgets($source));
     $SourceCounter++;
     if(strpos($line,"for")!==false)
      {
        echo "for Block on Line ".$SourceCounter."\n <pre>".htmlentities($line)." </pre>\n".
        '<h4>Iterations Number:</h4>
        <p><input class="form-control"  type="text" name="iterations[]"><br/>
        </p><hr>';

      }
    }
    fclose($source);
     echo '<input type="submit" value="Send" name="submit">
        </form>';
