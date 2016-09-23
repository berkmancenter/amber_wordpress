<?php

require_once("AmberNetworkUtils.php");

class NetworkUtilsTest extends \PHPUnit_Framework_TestCase {

  public function testHeadersParse()
  {
    $raw = <<<EOD
HTTP/1.1 200 OK
Content-Type: text/plain
Accept-Ranges: bytes
ETag: "1805770918"
Last-Modified: Tue, 20 Nov 2012 02:11:45 GMT
Content-Length: 611
nnCoection: close
Date: Thu, 29 May 2014 20:53:13 GMT
Connection: Keep-alive

EOD
;
    $headers = AmberNetworkUtils::extract_headers($raw);
    $this->assertEquals($headers["Content-Type"],"text/plain");
  }

  public function testHeadersParseCaseSensitive()
  {
    $raw = <<<EOD
HTTP/1.1 200 OK
Content-type: text/html
Accept-Ranges: bytes
ETag: "1805770918"
Last-Modified: Tue, 20 Nov 2012 02:11:45 GMT
Content-Length: 611
nnCoection: close
Date: Thu, 29 May 2014 20:53:13 GMT
Connection: Keep-alive

EOD
;
    $headers = AmberNetworkUtils::extract_headers($raw);
    $this->assertEquals($headers["Content-Type"],"text/html");
  }

  public function testHeadersParseValueHasColon()
  {
    $raw = <<<EOD
HTTP/1.1 200 OK
Content-type: text/html
Accept-Ranges: bytes
ETag: "1805770918"
Last-Modified: Tue, 20 Nov 2012 02:11:45 GMT
Content-Length: 611
nnCoection: close
Date: Thu, 29 May 2014 20:53:13 GMT
Connection: Keep-alive
Content-Location: http://somwhere.else

EOD
;
    $headers = AmberNetworkUtils::extract_headers($raw);
    $this->assertEquals($headers["Content-Location"],"http://somwhere.else");
  }

  public function testMimeTypeChecking(){
    $this->assertTrue(AmberNetworkUtils::is_html_mime_type("text/html"));    
    $this->assertFalse(AmberNetworkUtils::is_html_mime_type("image/jpg"));    
    $this->assertTrue(AmberNetworkUtils::is_html_mime_type("application/xhtml+xml"));    
  }

  public function testMetaTagExtraction() {
    $this->assertFalse(AmberNetworkUtils::find_meta_redirect(""));
    $this->assertFalse(AmberNetworkUtils::find_meta_redirect("bogus string"));
    $this->assertFalse(AmberNetworkUtils::find_meta_redirect(<<<EOD
<html>
<head><title>bad man</title></head>
<body>
Use META tags like this: <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</body>
</html>
EOD
));
    $this->assertEquals("http://www.example.org/login", AmberNetworkUtils::find_meta_redirect(<<<EOD
<html>
<head><title>bad man</title>
<meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</head>
<body>
Use META tags like this: <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</body>
</html>
EOD
));
    $this->assertEquals("http://www.example.org/login", AmberNetworkUtils::find_meta_redirect(<<<EOD
<html>
<head><title>bad man</title>
<meta http-equiv="REFRESH" content="0; url=http://www.example.org/login">
</head>
<body>
Use META tags like this: <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</body>
</html>
EOD
));
    $this->assertFalse(AmberNetworkUtils::find_meta_redirect(<<<EOD
<html>
<head><title>bad man</title>
<meta http-equiv="refresh" content="5">
</head>
<body>
Use META tags like this: <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</body>
</html>
EOD
));
        $this->assertEquals("http://www.example.org/login", AmberNetworkUtils::find_meta_redirect(<<<EOD
<html>
<head><title>bad man</title>
<meta http-equiv="REFRESH" content='0;url =  http://www.example.org/login'>
</head>
<body>
Use META tags like this: <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
</body>
</html>
EOD
));

  }

  public function testMetaNoArchiveTagDetectionNoTag() {
    $this->assertFalse(AmberNetworkUtils::find_meta_no_archive(""));
    $this->assertFalse(AmberNetworkUtils::find_meta_no_archive("bogus string"));
    $this->assertFalse(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title></head>
<body>
How are you doing?">
</body>
</html>
EOD
));
  }

  public function testMetaNoArchiveTagDetectionTagInBody() {
    $this->assertFalse(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title></head>
<body>
The meta tag only works in the head
<meta name="robots" content="noarchive">
</body>
</html>
EOD
));
  }

  public function testMetaNoArchiveTagDetectionSimple() {
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="robots" content="noarchive">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name='robots' content='noarchive'>
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name = 'robots' content = 'noarchive'>
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
  }

  public function testMetaNoArchiveTagDetectionAmberSpecific() {
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="amber" content="noarchive">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
  }

  public function testMetaNoArchiveTagDetectionNoIndex() {
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="robots" content="noindex">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="robots" content="noindex">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="amber" content="noarchive, noindex">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));
    $this->assertTrue(AmberNetworkUtils::find_meta_no_archive(<<<EOD
<html>
<head><title>bad man</title>
<meta name="robots" content="noindex,noarchive">
</head>
<body>
The meta tag only works in the head
</body>
</html>
EOD
));

  }

}