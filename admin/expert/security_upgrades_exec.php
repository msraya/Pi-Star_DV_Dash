<?php
$cmdoutput = array();
exec('sudo echo HELLO >> /tmp/trace.txt');
$cmdresult = exec('sudo /usr/bin/unattended-upgrade -d > /dev/null 2<&1', $cmdoutput, $retvalue);
echo "<br />";
foreach ($cmdoutput as $l) {
    echo $l."<br />";
}
if ($retvalue == 0) {
    echo "<h2>** Success **</h2>";
}
else {
    echo "<h2>!! Failure !!</h2>";
}
echo "<br />";
?>
