<?php
	include("config.php");
?>

<html>
<title>Billing System</title>
<head>
	<link rel="stylesheet" type="text/css" href="media/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	
	<script type="text/javascript" language="javascript" src="media/js/jquery.min.js"></script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js"></script>
	
	<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function() {
			startTime();
			periodik();
			$('#tabel').DataTable( {
				stateSave: true
			} );
		} );
		

		function startTime() {
			var today = new Date();
			var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
			var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
			
			var day = today.getDate();
			var month = today.getMonth();
			var thisDay = today.getDay(),
				thisDay = myDays[thisDay];
			var yy = today.getYear();
			var year = (yy < 1000) ? yy + 1900 : yy;
			var date = thisDay + ', ' + day + ' ' + months[month] + ' ' + year;
			
			var h = today.getHours();
			var m = today.getMinutes();
			var s = today.getSeconds();
			m = checkTime(m);
			s = checkTime(s);
			document.getElementById('clock').innerHTML = date+" - "+h + ":" + m + ":" + s + "\n";
			var t = setTimeout(startTime, 500);
			}
			
		function kirim(rek,mod,type)
		{ var r = confirm("Kirim Pesan ke Pengguna?");
		  if (r == true) {
				$.get("funct.php?f=1&rek="+rek+"&mod="+mod+"&type="+type,{},
					  function(data) {
						alert(data);
				});
				
		  if(type>2)
			 location.reload();
		}};
		
		function checkTime(i) {
			if (i < 10) {i = "0" + i};
			return i;
		}
		
		function controll(rek,mod,stat) {		
			$.get("funct.php?f=2&rek="+rek+"&mod="+mod+"&stat="+stat,{},
					function(data) {
						 location.reload();
					});	
		}
		
		function periodik() {		
			$.get("funct.php?f=3&mod=1",{},
					function(data) {
						if(data=='1')
							location.reload();
					});	
					
		    setTimeout(periodik, 5000);
		}
	</script>
	
</head>
 <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="">PLN Billing System</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class=""><a href="index.php">Ethernet</a></li>
			<li class="active"><a href="#">SMS</a></li>
          </ul>
		  <ul class="nav navbar-nav navbar-right">
            <li><div id="clock" class="navbar-brand txt-warning"></div></li>
          </ul>
        </div>
      </div>
    </nav>
	
<body class="dt-example">
	<table id="tabel" class="display" cellspacing="0" width="99.5%">
		<thead>
			<tr>
				<th><center>Nomor Rekening</center></th>
				<th><center>Nama Pemilik</center></th>
				<th><center>Periode</center></th>
				<th><center>Digunakan</center></th>
				<th><center>Biaya</center></th>
				<th><center>Status</center></th>
				<th><center>Action</center></th>
			</tr>
		</thead>
		<tfoot>
		</tfoot>
		<tbody>
			<?php
			$qry=mysql_query("SELECT * FROM billing");
			while($data=mysql_fetch_object($qry))
				 { echo "<tr>";
				   echo "<td align=center>$data->NoRek</td>";
				   echo "<td>$data->nama</td>";
				   echo "<td>".tanggal($data->month,$data->year)."</td>";
				   echo "<td>$data->kwh KWh</td>";
				   echo "<td>Rp. $data->bill</td>";
				   if($data->status=='1')
					  echo "<td class=label-success align=center><b>Aktif</b></td>";
				   else echo "<td class=label-danger align=center><b>Non Aktif</b></td>";
				   echo "<td align=center>
							<button class=btn-warning onclick='kirim(\"$data->NoRek\",\"1\",\"1\")'>Notifikasi</button>&nbsp;";
						if($data->status=='0')
							echo "<button class=btn-success onClick='controll(\"$data->NoRek\",\"1\",\"1\")'>Turn On</button>";
						else echo "<button class='btn-danger' onClick='controll(\"$data->NoRek\",\"1\",\"0\")'>Turn Off</button>";
				   echo "<br><br>
							 <button class=btn-primary onclick='kirim(\"$data->NoRek\",\"1\",\"2\")'>Warning</button>  
						     <button class=btn-primary onclick='kirim(\"$data->NoRek\",\"1\",\"3\")'>SP-1</button> 
							 <button class=btn-primary onclick='kirim(\"$data->NoRek\",\"1\",\"4\")'>SP-2</button>";
				   echo "</td></tr>"; 
				 }
			?>
		</tbody>
	</table>
</body>
</html>

<?php
	
function tanggal($month,$year)
{ 	$tgl="";
	
	switch($month)
    { 	 case "01" : $tgl="Januari ".$year;
					 break;
		 case "02" : $tgl="Februari ".$year;
					 break;
		 case "03" : $tgl="Maret ".$year;
					 break;
		 case "04" : $tgl="April ".$year;
					 break;
		 case "05" : $tgl="Mei ".$year;
					 break;
		 case "06" : $tgl="Juni ".$year;
					 break;
		 case "07" : $tgl="Juli ".$year;
					 break;
		 case "08" : $tgl="Agustus ".$year;
					 break;
		 case "09" : $tgl="September ".$year;
					 break;
		 case "10" : $tgl="Oktober ".$year;
					 break;
		 case "11" : $tgl="November ".$year;
					 break;
		 case "12" : $tgl="Desember ".$year;
					 break;
	}
	
	return $tgl;
}
?>