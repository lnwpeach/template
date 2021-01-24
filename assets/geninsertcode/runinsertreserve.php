<?php
  include("connect.php");

  $mthai[1] = "มกราคม";
  $mthai[2] = "กุมภาพันธ์";
  $mthai[3] = "มีนาคม";
  $mthai[4] = "เมษายน";
  $mthai[5] = "พฤษภาคม";
  $mthai[6] = "มิถุนายน";
  $mthai[7] = "กรกฎาคม";
  $mthai[8] = "สิงหาคม";
  $mthai[9] = "กันยายน";
  $mthai[10] = "ตุลาคม";
  $mthai[11] = "พฤศจิกายน";
  $mthai[12] = "ธันวาคม";

  $status[1] = 'รอชำระเงิน';
  $status[2] = 'ชำระเงินแล้ว';

  $sql = "select pro_id from \"6006021410172\".product";
  $parse = oci_parse($conn, $sql);
  oci_execute($parse, OCI_DEFAULT);
  $num = 0;
  while($rs = oci_fetch_array($parse, OCI_BOTH)) {
    $pro_id[] = $rs[0];
    $num++;
  }

  $sql = "select cust_id from customer where cust_id <= 'CT0020'";
  $parse = oci_parse($conn, $sql);
  oci_execute($parse, OCI_DEFAULT);
  $num2 = 0;
  while($rs = oci_fetch_array($parse, OCI_BOTH)) {
    $cust_id[] = $rs[0];
    $num2++;
  }

  $txtsql = "";
  $txtsql2 = "";
  $reserve_id = "0000001";
  $year = 2015;
  $mmax = 12;
  while($year <= 2018) {
    $month = 1;
    if($year == 2018)
      $mmax = 5;
    while($month <= $mmax) {
      for($i=1;$i<=5;$i++) {
        $date = rand(1, 28)." ".$mthai[$month]." ".$year;
        $time = sprintf("%02d", rand(0, 23)).":".sprintf("%02d", rand(0, 59));
        $sql = "insert into reserve values ('$reserve_id','$date','$time','".$cust_id[rand(0,$num2-1)]."', '".$status[rand(1,2)]."');";
        $txtsql .= $sql."<br>";
        //$parse = @oci_parse($conn, $sql);
        //$execute = @oci_execute($parse, OCI_DEFAULT);
        for($i=0;$i<=rand(1, 10);$i++) {
          $p = $pro_id[rand(0,$num-1)];
          $sql2 = "insert into reserve_detail values ('$reserve_id','".$p."','".rand(1, 5)."');";
          $txtsql2 .= $sql2."<br>";
          //$parse2 = @oci_parse($conn, $sql2);
          //$execute2 = @oci_execute($parse2, OCI_DEFAULT);
        }
        echo "<br>";
        $reserve_id = sprintf("%07d", ((int)$reserve_id+1));
        /*if($execute && $execute2) {
          oci_commit($conn);
          $reserve_id = sprintf("%07d", ((int)$reserve_id+1));
        }
        else {
          oci_rollback($conn);
          echo "Can't insert";
        }*/
      }
      echo "--------------<br>";
      $month++;
    }
    $year++;
  }
  echo $txtsql."<br>";
  echo $txtsql2;
  echo "Insert complete.";

?>
