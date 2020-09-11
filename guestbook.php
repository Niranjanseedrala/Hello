<?php
// disabling possible warnings
if (version_compare(phpversion(), "5.3.0", ">=")  == 1)
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
else
  error_reporting(E_ALL & ~E_NOTICE); 

require_once('classes/CMySQL.php'); // including service class to work with database

// get visitor IP
function getVisitorIP() {
    $ip = "0.0.0.0";
    if( ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) && ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif( ( isset( $_SERVER['HTTP_CLIENT_IP'])) && (!empty($_SERVER['HTTP_CLIENT_IP'] ) ) ) {
        $ip = explode(".",$_SERVER['HTTP_CLIENT_IP']);
        $ip = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0];
    } elseif((!isset( $_SERVER['HTTP_X_FORWARDED_FOR'])) || (empty($_SERVER['HTTP_X_FORWARDED_FOR']))) {
        if ((!isset( $_SERVER['HTTP_CLIENT_IP'])) && (empty($_SERVER['HTTP_CLIENT_IP']))) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }
    return $ip;
}

// get last guestbook records
function getLastRecords($iLimit = 3) {
    $sRecords = '';
    $aRecords = $GLOBALS['MySQL']->getAll("SELECT * FROM `s178_guestbook` ORDER BY `id` DESC LIMIT {$iLimit}");
    foreach ($aRecords as $i => $aInfo) {
        $sWhen = date('F j, Y H:i', $aInfo['when']);
        $sRecords .= <<<EOF
<div class="record" id="{$aInfo['id']}">
    <p>Record from {$aInfo['name']} <span>({$sWhen})</span>:</p>
    <p>{$aInfo['description']}</p>
</div>
EOF;
    }
    return $sRecords;
}

if ($_POST) { // accepting new records

    $sIp = getVisitorIP();
    $sName = $GLOBALS['MySQL']->escape(strip_tags($_POST['name']));
    $sEmail = $GLOBALS['MySQL']->escape(strip_tags($_POST['name']));
    $sDesc = $GLOBALS['MySQL']->escape(strip_tags($_POST['text']));

    if ($sName && $sEmail && $sDesc && $sIp) {

        // spam protection
        $iOldId = $GLOBALS['MySQL']->getOne("SELECT `id` FROM `s178_guestbook` WHERE `ip` = '{$sIp}' AND `when` >= UNIX_TIMESTAMP() - 600 LIMIT 1");
        if (! $iOldId) {

            // allow to add comment
            $GLOBALS['MySQL']->res("INSERT INTO `s178_guestbook` SET `name` = '{$sName}', `email` = '{$sEmail}', `description` = '{$sDesc}', `when` = UNIX_TIMESTAMP(), `ip` = '{$sIp}'");

            // drawing last 10 records
            $sOut = getLastRecords();
            echo $sOut;
            exit;
        }
    }
    echo 1;
    exit;
}

// drawing last 10 records
$sRecords = getLastRecords();

ob_start();
?>
<div class="container" id="records">
    <div id="col1">
        <h2>Guestbook Records</h2>
        <div id="records_list"><?= $sRecords ?></div>
    </div>

    <div id="col2">
        <h2>Add your record here</h2>
        <script type="text/javascript">
        function submitComment(e) {
            var name = $('#name').val();
            var email = $('#email').val();
            var text = $('#text').val();

            if (name && email && text) {
                $.post('guestbook.php', { 'name': name, 'email': email, 'text': text }, 
                    function(data){ 
                        if (data != '1') {
                          $('#records_list').fadeOut(1000, function () { 
                            $(this).html(data);
                            $(this).fadeIn(1000); 
                          }); 
                        } else {
                          $('#warning2').fadeIn(2000, function () { 
                            $(this).fadeOut(2000); 
                          }); 
                        }
                    }
                );
            } else {
              $('#warning1').fadeIn(2000, function () { 
                $(this).fadeOut(2000); 
              }); 
            }
        };
        </script>

        <form onsubmit="submitComment(this); return false;">
            <table>
                <tr><td class="label"><label>Your name: </label></td><td class="field"><input type="text" value="" title="Please enter your name" id="name" /></td></tr>
                <tr><td class="label"><label>Your email: </label></td><td class="field"><input type="text" value="" title="Please enter your email" id="email" /></td></tr>
                <tr><td class="label"><label>Comment: </label></td><td class="field"><textarea name="text" id="text" maxlength="255"></textarea></td></tr>
                <tr><td class="label">&nbsp;</td><td class="field">
                    <div id="warning1" style="display:none">Don`t forget to fill all required fields</div>
                    <div id="warning2" style="display:none">You can post no more than one comment every 10 minutes (spam protection)</div>
                    <input type="submit" value="Submit" />
                </td></tr>
            </table>
        </form>
    </div>
</div>
<?
$sGuestbookBlock = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="en" >
    <head>
        <meta charset="utf-8" />
        <title>PHP guestbook | Script Tutorials</title>

        <link href="css/main.css" rel="stylesheet" type="text/css" />
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <script src="http://code.jquery.com/jquery-latest.min.js"></script>
    </head>
    <body>
        <?= $sGuestbookBlock ?>
        <footer>
            <h2>PHP guestbook</h2>
            <a href="http://www.script-tutorials.com/php-guestbook/" class="stuts">Back to original tutorial on <span>Script Tutorials</span></a>
        </footer>
    </body>
</html>