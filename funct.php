<?php
include("config.php");
extract($_GET);

switch($f)
{	case '1' :	warn($rek,$mod,$type);
				break;
	case '2' :	dev_crtl($rek,$mod,$stat);
				break;
	case '3' :	auto_check($mod);
				break;
	case '0' :	test();
				break;
}

/*	keterangan variabel :
	$rek 	: nomor rekening
	$state 	: 0 = mati, else nyala
	$mod 	: 0 = mode ethernet, else mode sms
	$type	: tipe pesan, 1=notif, 2=hari H, 3=SP1, 4=SP2
			
*/

function warn($rek,$mod,$type)
{	$data=mysql_fetch_object(mysql_query("SELECT * FROM billing WHERE NoRek='$rek'"));
	$period=get_period($rek,0);
	$msg="";
	
	switch($type)
	{	case '1'	: 	$msg="Rekening listrik periode $period atas nama $data->nama, ".
							 "pengunaan $data->kwh"."KWh dengan biaya Rp.$data->bill, ".
							 "Pembayaran paling lambat 20 ".get_period($rek,1);
						echo "Notifikasi Terkirim!";
						break;
		case '2'	:	$msg="Jatuh tempo pembayaran rekening listrik periode $period atas nama $data->nama, ".
							 "pengunaan $data->kwh"."KWh dengan biaya Rp.$data->bill";
						echo "Peringatan Terkirim!";
						break;
		case '3'	: 	$msg="Peringatan pertama keterlambatan pembayaran rekening listrik periode $period atas nama ".
							 "$data->nama. dilakukan pemutusan jalur sementara.";
						echo "SP1 Terkirim!";
						dev_crtl($rek,$mod,"0");
						break;
		case '4'	:	$msg="Peringatan kedua keterlambatan pembayaran rekening listrik periode $period atas nama ".
							 "$data->nama. dilakukan pemutusan jalur PERMANEN.";
						echo "SP2 Terkirim!";
						dev_crtl($rek,$mod,"0");
						break;
	}
	
	send_sms($data->phone,$msg);
}

function send_sms($numb,$msg)
{	mysql_query("INSERT INTO outbox (DestinationNumber, TextDecoded, CreatorID) VALUES ('$numb', '$msg', 'Gammu')");
}

function dev_crtl($rek,$mod,$stat)
{ 	$data=mysql_fetch_object(mysql_query("SELECT * FROM billing WHERE NoRek='$rek'"));

	if(!$stat) 
	  {	mysql_query("UPDATE billing set status=0 WHERE NoRek LIKE $rek");
		if($mod) send_sms($data->dev_phone,"of");
	  }
	else {	mysql_query("UPDATE billing set status=1 WHERE NoRek LIKE $rek");
			if($mod) send_sms($data->dev_phone,"on");
		 }
}

function get_period($rek,$gap)
{ 	$data=mysql_fetch_object(mysql_query("SELECT * FROM billing WHERE NoRek='$rek'"));
	$tgl="";
	
	switch($data->month+$gap)
    { 	 case "01" : $tgl="Januari ".$data->year;
					 break;
		 case "02" : $tgl="Februari ".$data->year;
					 break;
		 case "03" : $tgl="Maret ".$data->year;
					 break;
		 case "04" : $tgl="April ".$data->year;
					 break;
		 case "05" : $tgl="Mei ".$data->year;
					 break;
		 case "06" : $tgl="Juni ".$data->year;
					 break;
		 case "07" : $tgl="Juli ".$data->year;
					 break;
		 case "08" : $tgl="Agustus ".$data->year;
					 break;
		 case "09" : $tgl="September ".$data->year;
					 break;
		 case "10" : $tgl="Oktober ".$data->year;
					 break;
		 case "11" : $tgl="November ".$data->year;
					 break;
		 case "12" : $tgl="Desember ".$data->year;
					 break;
	}
	
	return $tgl;
}

function auto_check($mod)
{	$qry=mysql_query("SELECT * FROM billing");
	$data1=mysql_num_rows($qry);
	$data2=mysql_num_rows(mysql_query("SELECT * FROM warning"));	
	
	if($data2<$data1)
	 while($data3=mysql_fetch_object($qry))
		   mysql_query("INSERT INTO warning VALUES('$data3->NoRek','0','0','0')");
	   
	$cmonth = date("m"); $cday = date("d");
	
	while($bill=mysql_fetch_object($qry))
	{	$warn=mysql_fetch_object(mysql_query("SELECT * FROM warning WHERE NoRek LIKE '$bill->NoRek'"));
		
		if($cday>='20' && abs($cmonth-$bill->month)==1)	//Hari H
		   if($warn->H1=='0')
			 {  warn($bill->NoRek,$mod,'2');
				mysql_query("UPDATE warning SET H1='".$cmonth."-".$cday."',SP2='0' WHERE NoRek LIKE '$bill->NoRek'");
			 }
			 
		if($cday>='20' && abs($cmonth-$bill->month)>=2 && abs($cmonth-$bill->month)<4)	//SP1
		   if($warn->SP1=='0')
			 {  warn($bill->NoRek,$mod,'3');
				mysql_query("UPDATE warning SET SP1='".$cmonth."-".$cday."',H1='0' WHERE NoRek LIKE '$bill->NoRek'");
			 }
		
		if($cday>='20' && abs($cmonth-$bill->month)>=4)	//SP2
		   if($warn->SP2=='0')
			 {  warn($bill->NoRek,$mod,'4');
				mysql_query("UPDATE warning SET SP2='".$cmonth."-".$cday."',SP1='0' WHERE NoRek LIKE '$bill->NoRek'");
			 }
	}
	
}

function test()
{
}
?>