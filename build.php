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
  system(
    "svn export http://gallery.svn.sourceforge.net/svnroot/gallery/gallery3/tags/$tag " .
    "tmp/gallery3")
    or die("export failed");
}

function prune() {
  system("rm -rf tmp/gallery3/modules/gallery_unit_test");
  system("rm -rf tmp/gallery3/modules/unit_test");
  system("rm -rf tmp/gallery3/modules/*/tests");
  system("rm -rf tmp/gallery3/core/tests");
}

function package($tag) {
  chdir("tmp");
  system("zip -r ../dist/{$tag}.zip gallery3");
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