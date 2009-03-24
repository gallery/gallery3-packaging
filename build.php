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

function package($package_name) {
  chdir("tmp");

  system("zip -r ../dist/{$package_name}.zip gallery3");
}


$tag = $_SERVER['argv'][1];
if (empty($tag)) {
  print "Usage: build.php <tag>\n";
  exit(1);
}

// Convert RELEASE_3_0_ALPHA_3 to gallery-3.0-alpha-2.zip
preg_match("/RELEASE_(\d+)_(\d+)_(.*)/", $tag, $matches);
list ($major, $minor, $build) = array($matches[1], $matches[2], $matches[3]);
$build = strtolower(strtr($build, "_", "-"));
$package_name = "gallery-$major.$minor-$build";

clean();
prepare();
export($tag);
prune();
package($package_name);
?>