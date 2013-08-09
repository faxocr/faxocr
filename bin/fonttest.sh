#!/bin/sh

TMPNAME=fonttest

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
  <meta http-equiv=\"Content-Style-Type\" content=\"text/css\" />
  <meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\" />
  <title>Shinsai FaxOCR</title>
</head>
<body>
<style type=\"text/css\">
<!--
body,td,th {
	font-size: normal;
}

.XF {
border-top-width: 1px;
border-top-style: solid;
border-top-color: #444444;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #444444;
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #444444;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #444444;
}

.XF0 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
vertical-align: middle;
}
.XF1 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF2 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF3 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF4 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF5 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF6 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF7 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF8 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF9 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF10 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF11 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF12 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF13 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF14 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF15 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF16 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF17 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF18 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF19 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF20 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF21 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF22 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF23 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF24 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF25 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF26 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF27 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF28 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF29 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF30 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF31 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF32 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF33 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF34 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF35 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF36 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF37 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF38 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF39 {
color: #FFFFFF;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF40 {
font:  bold 22.5px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF41 {
border-bottom-width: 3px;
border-bottom-style: double;
border-bottom-color: #000000;
border-right-width: 3px;
border-right-style: double;
border-right-color: #000000;
border-top-width: 3px;
border-top-style: double;
border-top-color: #000000;
border-left-width: 3px;
border-left-style: double;
border-left-color: #000000;
color: #FFFFFF;
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF42 {
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF43 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF44 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #C0C0C0;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #C0C0C0;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #C0C0C0;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #C0C0C0;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF45 {
border-bottom-width: 3px;
border-bottom-style: double;
border-bottom-color: #FF9900;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #FF9900;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF46 {
color: #800080;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF47 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #808080;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #808080;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #808080;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #808080;
color: #FF9900;
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF48 {
color: #FF0000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF49 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF50 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF51 {
border-bottom-width: 3px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  bold 18.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF52 {
border-bottom-width: 3px;
border-bottom-style: solid;
border-bottom-color: #C0C0C0;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  bold 16.25px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF53 {
border-bottom-width: 2px;
border-bottom-style: solid;
border-bottom-color: #0066CC;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF54 {
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF55 {
border-bottom-width: 3px;
border-bottom-style: double;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF56 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  bold 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF57 {
color: #808080;
font:  italic 13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF58 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF59 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF60 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #808080;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #808080;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #808080;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #808080;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF61 {
color: #008000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF62 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  bold 25px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF63 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
vertical-align: middle;
}
.XF64 {
font:  bold 25px \"HG創英角ｺﾞｼｯｸUB\";
text-align: center;
vertical-align: middle;
}
.XF65 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF66 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  17.5px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF67 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF68 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
vertical-align: middle;
}
.XF69 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
white-space: nowrap;
}
.XF70 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
vertical-align: middle;
}
.XF71 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF72 {
border-bottom-width: 1px;
border-bottom-style: dotted;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: dotted;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: dotted;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: dotted;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
white-space: nowrap;
}
.XF73 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF74 {
font:  30px \"OCRB\";
vertical-align: middle;
}
.XF75 {
border-bottom-width: 2px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 2px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 2px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 2px;
border-left-style: solid;
border-left-color: #000000;
color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XF76 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF77 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF78 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF79 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF80 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XF81 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XF82 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF83 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  17.5px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF84 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＨＧｺﾞｼｯｸE-PRO\";
text-align: center;
vertical-align: middle;
}
.XF85 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-decoration: underline;
text-align: left;
vertical-align: middle;
}
.XF86 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF87 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF88 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF89 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XF90 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF91 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  15px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF92 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XF93 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF94 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF95 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XF96 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c15 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c1 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c3 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c5 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c9 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c11 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c7 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r8c13 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  30px \"OCRB\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c9 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c11 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c7 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c13 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c5 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r7c3 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r0c13 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  15px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XFs0r10c8 {
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XFs0r4c18 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-decoration: underline;
text-align: left;
vertical-align: middle;
}
.XFs0r4c2 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＨＧｺﾞｼｯｸE-PRO\";
text-align: center;
vertical-align: middle;
}
.XFs0r4c4 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-decoration: underline;
text-align: left;
vertical-align: middle;
}
.XFs0r4c16 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＨＧｺﾞｼｯｸE-PRO\";
text-align: center;
vertical-align: middle;
}
.XFs0r6c15 {
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r6c1 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XFs0r6c3 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r6c11 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r6c7 {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #000000;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #000000;
border-top-width: 1px;
border-top-style: solid;
border-top-color: #000000;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #000000;
font:  13.75px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}
.XFs0r1c3 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
color: #000000;
font:  13.75px \"ＭＳ Ｐゴシック\";
text-align: center;
vertical-align: middle;
}
.XFs0r2c5 {
border-bottom-color: #000000;
border-right-color: #000000;
border-top-color: #000000;
border-left-color: #000000;
font:  17.5px \"ＤＦ平成ゴシック体W9\";
text-align: center;
vertical-align: middle;
}


-->
</style>

<div id=\"ex3\" class=\"jqDnR\" style=\"top:0px; left:0px; z-index: 3; position: relative; width: 960px; height:610.90909090909px; font-size: 12px; \">
<img src=\"/home/faxocr/etc/mark.gif\" class=\"mark-img\" style=\"position: absolute; top: 0;left: 0; width: 41.454545454545px;\"><div style=\"position: absolute; left:82.909090909091px; \"><font style=\"line-height: 41.454545454545px; font-size: 41.454545454545px; font-family: 'OCRB'; \">00000</font></div>
<img src=\"/home/faxocr/etc/mark.gif\" class=\"mark-img\" style=\"position: absolute; top: 0;right: 0; width: 41.454545454545px;\">
<img src=\"/home/faxocr/etc/mark.gif\" class=\"mark-img\" style=\"position: absolute; bottom: 0;left: 0; width: 41.454545454545px;\"><div style=\"position: absolute; left:82.909090909091px; bottom: 0\"><font style=\"line-height: 41.454545454545px; font-size: 41.454545454545px; font-family: 'OCRB'; \">00007</font></div>
<table class=\"sheet\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"960\" height=\"610.90909090909\" style=\"table-layout:fixed; border-collapse: collapse;\" bgcolor=\"#FFFFFF\" >
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF62\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF63\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF63\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF63\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF63\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF64\"  ></td>
 <td  class=\"XFs0r0c13\"  colspan=\"8\" >&larr; この方向でFAXして下さい</td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  >FAX</td>
 <td  class=\"XFs0r1c3\"  colspan=\"3\" >050-3488-7974</td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF66\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td  class=\"XFs0r2c5\"  colspan=\"14\" >日次報告</td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td  class=\"XFs0r4c2\"  colspan=\"2\" >調査日</td>
 <td  class=\"XFs0r4c4\"  colspan=\"5\" >　　　　　月　　　日　（　　　）</td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td  class=\"XFs0r4c16\"  colspan=\"2\" >病院名</td>
 <td  class=\"XFs0r4c18\"  colspan=\"3\" >A窓口</td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td  class=\"XFs0r6c1\"  colspan=\"2\" rowspan=\"2\" ></td>
 <td  class=\"XFs0r6c3\"  colspan=\"4\" >分類1</td>
 <td  class=\"XFs0r6c7\"  colspan=\"4\" >分類2</td>
 <td  class=\"XFs0r6c11\"  colspan=\"4\" >分類3</td>
 <td  class=\"XFs0r6c15\"  colspan=\"6\" rowspan=\"2\" >備考</td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td  class=\"XFs0r7c3\"  colspan=\"2\" >Aタイプ</td>
 <td  class=\"XFs0r7c5\"  colspan=\"2\" >Bタイプ</td>
 <td  class=\"XFs0r7c7\"  colspan=\"2\" >Cタイプ</td>
 <td  class=\"XFs0r7c9\"  colspan=\"2\" >Dタイプ</td>
 <td  class=\"XFs0r7c11\"  colspan=\"2\" >Eタイプ</td>
 <td  class=\"XFs0r7c13\"  colspan=\"2\" >Fタイプ</td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td  class=\"XFs0r8c1\"  colspan=\"2\" >報告数</td>
 <td  class=\"XFs0r8c3\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c5\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c7\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c9\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c11\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c13\"  colspan=\"2\" ></td>
 <td  class=\"XFs0r8c15\"  colspan=\"6\" ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF68\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF68\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF69\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF70\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF70\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF70\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF70\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td  class=\"XFs0r10c8\"  colspan=\"8\" >翌日が休日の場合は、右欄に「○」をご記入下さい</td>
 <td nowrap=\"nowrap\"  class=\"XF71\"  >&rArr;</td>
 <td nowrap=\"nowrap\"  class=\"XF75\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF67\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF73\"  >管理</td>
</tr>
  <tr height=\"43.636363636364\">
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF74\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF65\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF15\"  ></td>
 <td nowrap=\"nowrap\"  class=\"XF72\"  ></td>
</tr>
</table>
</div>
</body>
</html>
" > $TMPNAME.html

xvfb-run -a wkhtmltopdf --page-size A4 -O Landscape $TMPNAME.html $TMPNAME.pdf
if [ `strings $TMPNAME.pdf | grep OCRB |wc -l ` -ge 1 ]; then
        echo found OCRB font
else
        echo NOT found OCRB font
fi
rm -f $TMPNAME.html
