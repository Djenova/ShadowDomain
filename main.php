<?php

/**
 * Class  Utama Pada Proses
 */
//include_once 'conf/db.conf.php';
include("simple_html_dom.php");
class Main
{

  public function GrabDomains ($id,$password)
  {
    date_default_timezone_set("UTC");
    $date = date("Y-m-d");
    $ch = curl_init();
    $postData = array(
        'login' => $id,
        'password' => $password
    );
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://member.expireddomains.net/login/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_FOLLOWLOCATION => true,
        //CURLOPT_COOKIESESSION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7',
        CURLOPT_COOKIEJAR => 'cookie.txt',
        CURLOPT_COOKIEFILE => 'cookie.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST =>false,
        //CURLOPT_URL => 'https://member.expireddomains.net/domains/expiredcom/'
    ));
    $output = curl_exec($ch);
    curl_setopt_array($ch, array(
        //CURLOPT_URL => 'https://member.expireddomains.net/export/expiredcom/?export=textfile&&flast12=1flimit=25&fstatuscomfree=22&fstatusorgfree=22&fstatusinfofree=22&fadddate='.$date.'&fstatususfree=22&fstatuscofree=22',
        //Development Mode
        CURLOPT_URL => 'https://member.expireddomains.net/export/expiredcom/?export=textfile&flimit=25&fadddate=2017-06-27',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7',
    ));


    $content = curl_exec($ch);
    return $content;
  }

  public function ExplodeDomain($data)
  {
    $deleteextention = str_replace(".com","",$data);
    //$string = trim(preg_replace('/\s+/', ' ', $deleteextention));
    //$destroy = explode(":",$deleteextention);
    //print_r($explode);
    //echo "<pre>".$deleteextention."</pre>";
    return $deleteextention;
  }

  public function SaveDomainTxt($data)
  {
    $tanggal = date("Y-m-d");
    $myfile = fopen("dump/".$tanggal.".txt", "w") or die("Unable to open file!");
    fwrite($myfile, $data);
    fclose($myfile);
  }

  public function ProxyHandler($proxy_id =0)
  {
    $all_proxies = array(
        // below is an dummy / example proxy, add the rest from your list here
       0 => array(
            'server' => '113.53.230.195',
            'port' => '3128'
       ),
       1 => array(
            'server' => 'tcp://104.236.238.10',
            'port' => '3128'
       ),
       3 => array(
            'server' => '103.195.142.88',
            'port' => '9999'
       ),
       4 => array(
            'server' => '188.166.216.210',
            'port' => '3128'
       )
     );
     $proxies_count = count($all_proxies);
     if ($proxy_id == 0) {
       $proxy_id = rand(1, $proxies_count);
     }
     $proxy = array();
     $proxy['server']   = $all_proxies[$proxy_id]['server'];
     $proxy['port']     = $all_proxies[$proxy_id]['port'];
     return $proxy;
  }

  public function Scraper($domain, $use_proxy=FALSE)
  {
    $in = "site:".$domain;
    $in = str_replace(' ','+',$in); // space is a +
    $url  = 'http://www.google.com/search?hl=en&tbo=d&site=&num=50&source=hp&q='.$in.'&oq='.$in.'';

    //print $url."<br>";
    if ($use_proxy) {
      $proxy = Main::ProxyHandler($proxy_id =1);
      $context      = array(
            'http' => array(
                'proxy' => $proxy['server'].':'.$proxy['port'], // This needs to be the server and the port of the NTLM Authentication Proxy Server.
                //'request_fulluri' => true

            )
      );
      $context = stream_context_create($context);
      $html = file_get_html($url, FALSE, $context);
      echo "Connected via".$proxy['server'].":".$proxy['port'];

    } else {
      $html = file_get_html($url);
    }

    $i=0;
    $linkObjs = $html->find('h3.r a');
    foreach ($linkObjs as $linkObj) {
        $title = trim($linkObj->plaintext);
        $link  = trim($linkObj->href);

        // if it is not a direct link but url reference found inside it, then extract
        if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
            $link = $matches[1];
        } else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
            continue;
        }

        $descr = $html->find('span.st',$i); // description is not a child element of H3 thereforce we use a counter and recheck.
        $i++;
        //echo '<p>Title: ' . $title . '<br />';
        //echo 'Link: ' . $link . '<br />';
        //echo 'Description: ' . $descr . '</p>';S
        //  $datalink[] = $link;
        //  echo 'Link: ' . $link . '<br />';
        echo 'Link: ' . $link . '<br />';
        //$data = $link;
        //$tanggal = date("Y-m-d");
        //if (strpos($link,$domain) !== false){
          //$datalink[$i] = $domain;
          //echo $link;
          //$datalink[] = $domain;
          //file_put_contents("scrap/com.".$tanggal.".txt",$domain,FILE_APPEND);
          //break;
        //}


        //$myfile = fopen("scrap/com.".$tanggal.".txt", "w") or die("Unable to open file!");
        //fwrite($myfile, print_r($valid));
        //fclose($myfile);
    }
    //print_r($datalink);

  }

  public function MozCheck($domain)
  {
    $accessID = "mozscape-6f077f0071";
    $secretKey = "8a1730862e52e63e48d6defb21202165";
    $expires = time() + 300;
    $stringToSign = $accessID."\n".$expires;
    $binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
    $urlSafeSignature = urlencode(base64_encode($binarySignature));
    $cols = "103146324244";
    $requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/".urlencode($domain)."?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;
    $options = array(
    	CURLOPT_RETURNTRANSFER => true
    	);

    $ch = curl_init($requestUrl);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    //Filter
    $raw = json_decode($content);
    $pda = $raw->pda;
    $upa = $raw->upa;
    $spam = $raw->fspsc;
    $domain = trim($domain," <br>");
    if (($pda>=10) && ($pda<=45) && ($upa>=10) && ($upa<=45) && ($spam>=1) && ($spam<=5)) {
      Main::SaveMoz($domain,$pda,$upa,$spam);
    } else {
      Main::SaveFailedMoz($domain,$pda,$upa,$spam);
    }

  }
  //Ndak jadi di pakek
  /*public function MozFilter($data)
  {
    $raw = json_decode($data);
    $pda = $raw->pda;
    $upa = $raw->upa;
    $spam = $raw->fspsc;
    $domain = $raw->upl;
    if (($pda>=10) && ($pda<=45) && ($upa>=10) && ($upa<=45) && ($spam>=1) && ($spam<=5)) {
      Main::SaveMoz($domain,$pda,$upa,$spam);
    } else {
      Main::SaveFailedMoz($domain,$pda,$upa,$spam);
    }
  }*/
  public function SaveMoz($domain,$pda,$upa,$spam)
  {
    $tanggal = date("Y-m-d");
    $toSave = $domain.":".$pda.":".$upa.":".$spam;
    $file = "moz/Sucsess-$tanggal.txt" ;
    $file2 = "moz/Buy-$tanggal.txt";
    file_put_contents($file, $toSsve . PHP_EOL, FILE_APPEND);
    file_put_contents($file2, $domain  . PHP_EOL, FILE_APPEND);
  }

  public function SaveFailedMoz($domain,$pda,$upa,$spam)
  {
    $tanggal = date("Y-m-d");
    $toSave = $domain.":".$pda.":".$upa.":".$spam;
    $file = "moz/Failed-$tanggal.txt" ;
    file_put_contents($file, $toSave . PHP_EOL, FILE_APPEND);
  }

  public function BuyDomain($domain)
  {
    $tanggal = date("Y-m-d");
    $data = "https://api.sandbox.namecheap.com/xml.response?ApiUser=ahgryd&ApiKey=4ddbec4e45bc4b00939e41ebc813b193&UserName=ahgryd&Command=namecheap.domains.create&ClientIp=192.168.1.109&DomainName=".$domain."&Years=1&AuxBillingFirstName=John&AuxBillingLastName=Smith&AuxBillingAddress1=8939%20S.cross%20Blv&AuxBillingStateProvince=CA&AuxBillingPostalCode=90045&AuxBillingCountry=US&AuxBillingPhone=+1.6613102107&AuxBillingEmailAddress=john@gmail.com&AuxBillingOrganizationName=NC&AuxBillingCity=CA&TechFirstName=John&TechLastName=Smith&TechAddress1=8939%20S.cross%20Blvd&TechStateProvince=CA&TechPostalCode=90045&TechCountry=US&TechPhone=+1.6613102107&TechEmailAddress=john@gmail.com&TechOrganizationName=NC&TechCity=CA&AdminFirstName=John&AdminLastName=Smith&AdminAddress1=8939%cross%20Blvd&AdminStateProvince=CA&AdminPostalCode=9004&AdminCountry=US&AdminPhone=+1.6613102107&AdminEmailAddress=joe@gmail.com&AdminOrganizationName=NC&AdminCity=CA&RegistrantFirstName=John&RegistrantLastName=Smith&RegistrantAddress1=8939%20S.cross%20Blvd&RegistrantStateProvince=CS&RegistrantPostalCode=90045&RegistrantCountry=US&RegistrantPhone=+1.6613102107&RegistrantEmailAddress=jo@gmail.com&RegistrantOrganizationName=NC&RegistrantCity=CA&AddFreeWhoisguard=no&WGEnabled=True&GenerateAdminOrderRefId=False&IsPremiumDomain=False";
    $xml=simplexml_load_file($data) or die("Error: Cannot create object");
    $status = $xml->attributes()->Status;
    $dDomain = $xml->CommandResponse->DomainCreateResult->attributes()->Domain;
    if ($status == "OK") {
      $file = "namecheap/Success-$tanggal.txt" ;
      file_put_contents($file, $domain . PHP_EOL, FILE_APPEND);
    } else {
      $file = "namecheap/Failed-$tanggal.txt" ;
      file_put_contents($file, $domain . PHP_EOL, FILE_APPEND);
    }
  }

}

 ?>
