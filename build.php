#!/usr/bin/php -f
<?php
function my_system($cmd) {
  print "Cmd> $cmd\n";
  return system($cmd);
}

function clean() {
  my_system("rm -rf tmp");
}

function prepare() {
  foreach (array("tmp", "dist") as $dir) {
    if (!file_exists($dir)) {
      mkdir($dir);
    }
  }
}

function export($tag) {
  $dir = getcwd();
  my_system("git clone git://github.com/gallery/gallery3.git tmp/gallery3")
    or die("git clone failed");

  chdir("tmp/gallery3");

  print "=================================================\n";
  my_system("git checkout $tag");  // this isn't a branch, so it'll succeed with an error.
  print "=================================================\n";
  print " The above command will fail with a message like \n";
  print " '$tag isn't a local branch'\n";
  print " that's ok-- you can ignore it\n";
  print "=================================================\n";
  chdir($dir);
}

function prune() {
  my_system("rm -rf tmp/gallery3/modules/gallery_unit_test");
  my_system("rm -rf tmp/gallery3/modules/unit_test");
  my_system("rm -rf tmp/gallery3/modules/*/tests");
  my_system("rm -rf tmp/gallery3/core/tests");
  my_system("rm -rf tmp/gallery3/core/controllers/scaffold.php");
  my_system("rm -rf tmp/gallery3/.git");
}

function package($tag) {
  chdir("tmp");

  my_system("zip -q --exclude .gitignore -r ../dist/gallery-{$tag}.zip gallery3");
}


$tag = $_SERVER['argv'][1];
if (empty($tag)) {
  print "Usage: build.php <tag>\n";
  exit(1);
}

clean();
prepare();
export($tag);
prune();
package($tag);
?>