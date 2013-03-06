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

function convert_readme_to_txt() {
  $readme = "tmp/gallery3/README.md";
  $data = file_get_contents($readme);

  // Remove Travis build badge.
  $data = preg_replace(
    "/\n\n\[!\[Build(.*?)travis\-ci\.org\/gallery\/gallery3\)/",
    "",
    $data, 1, $count);
  if ($count != 1) {
    print "README: Unable to remove Travis build badge\n";
    exit(1);
  }

  // Multiline code blocks should have some space around them for readability.
  if (preg_match("/```\n[^\n]|[^\n]\n```\w+?\n/", $data)) {
    print "README: Multiline code blocks must be surrounded by empty lines\n";
    exit(1);
  }

  // Remove Markdown elements.
  $data = preg_replace(array(
    "/```\w*?\n/",           // multiline code blocks
    "/\*{1,2}(.*?)\*{1,2}/", // emphasis
    "/\`(.*?)\`/"),          // intraline code blocks
    array("", "\\1", "\\1"),
    $data);

  // Remove Heading 3 hashes and make title uppercase.
  $data = preg_replace_callback(
    "/###( .*\n)/",
    create_function('$matches', 'return strtoupper($matches[1]);'),
    $data);

  if (file_put_contents(str_replace(".md", "", $readme), $data) == false) {
    print "Could not save README to disk\n";
    exit(1);
  }
}

function prune() {
  my_system("rm -rf tmp/gallery3/modules/gallery_unit_test");
  my_system("rm -rf tmp/gallery3/modules/unit_test");
  my_system("rm -rf tmp/gallery3/modules/*/tests");
  my_system("rm -rf tmp/gallery3/core/tests");
  my_system("rm -rf tmp/gallery3/core/controllers/scaffold.php");
  my_system("rm -rf tmp/gallery3/.git");
  my_system("rm -rf tmp/gallery3/.build_number");
  my_system("rm -rf tmp/gallery3/.gitattributes");
  my_system("rm -rf tmp/gallery3/.travis.yml");
  my_system("rm -rf tmp/gallery3/README.md");
  my_system("rm `find tmp/gallery3 -name .gitignore`");
}

function fix_release_channel() {
  $gallery_helper = "tmp/gallery3/modules/gallery/helpers/gallery.php";
  $data = file_get_contents($gallery_helper);
  $data = preg_replace(
    '/const RELEASE_CHANNEL = "git";/',
    'const RELEASE_CHANNEL = "release";',
    $data, 1);
  file_put_contents($gallery_helper, $data);
  if (($result = `php -l "$gallery_helper"`) !=
      "No syntax errors detected in $gallery_helper\n") {
    print "Error changing release channel:\n$result";
    exit(1);
  }

  $script = join(";", array("define(SYSPATH, 1)",
                            "include \"$gallery_helper\"",
                            "print gallery_Core::RELEASE_CHANNEL"));
  $channel = `php -r '$script;'`;
  if ($channel != "release") {
    print "Error fixing release channel\n";
    print $channel;
    exit(1);
  }
}

function package($tag) {
  @unlink("dist/gallery-{$tag}.zip");
  chdir("tmp");

  my_system("zip -q -r ../dist/gallery-{$tag}.zip gallery3");
}


$tag = $_SERVER['argv'][1];
if (empty($tag)) {
  print "Usage: build.php <tag>\n";
  exit(1);
}

clean();
prepare();
export($tag);
convert_readme_to_txt();
prune();
fix_release_channel();
package($tag);
?>
