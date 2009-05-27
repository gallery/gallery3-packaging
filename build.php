#!/usr/bin/php -f
<?php
function clean() {
  system("rm -rf tmp");
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
  system("git clone git://github.com/gallery/gallery3.git tmp/gallery3")
    or die("git clone failed");

  chdir("tmp/gallery3");
  // I had to remove the die(...) as on my installation it returns with
  // a warning and stops.
  system("git checkout $tag");
  chdir($dir);
}

function prune() {
  system("rm -rf tmp/gallery3/modules/gallery_unit_test");
  system("rm -rf tmp/gallery3/modules/unit_test");
  system("rm -rf tmp/gallery3/modules/*/tests");
  system("rm -rf tmp/gallery3/core/tests");
  system("rm -rf tmp/gallery3/core/controllers/scaffold.php");
  system("rm -rf tmp/gallery3/.git");
  system("find tmp/gallery3 -name .gitignore | xargs rm");
}

function package($tag) {
  chdir("tmp");

  system("zip -r ../dist/gallery-{$tag}.zip gallery3");
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