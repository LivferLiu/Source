<?php
    function bubbleSort($arr){
      $count = count($arr);
      $temp = array();
      $flag = false;
      for($i = 0; $i < $count -1; $i++){
      	for ($j=0; $j < $count - 1 - $i ; $j++) { 
      		# code...
      		if($arr[$j] > $arr[$j+1]){
      			$temp = $arr[$j];
      			$arr[$j] = $arr[$j+1];
      			$arr[$j+1] = $temp;
      			$flag = true;
      		}
      	}
      	if(!$flag){
      		break;
      	}
      	$flag = false;
      }
      return $arr;
    }

    function selectionSort($arr){
    	$count = count($arr);
    	$temp = array();
    	for($i = 0; $i < $count -1; $i++){
    		$minVal = $arr[$i];
    		$minIndex = $i;
    		for($j = $i+1; $j < $count; $j++){
    			if($minVal > $arr[$j]){
    				$minVal = $arr[$j];
    				$minIndex = $j;
    			}
    		}
    		$temp = $arr[$i];
    		$arr[$i] = $arr[$minIndex];
    		$arr[$minIndex] = $temp;
    	}
    	return $arr;
    }

    function insertSort($arr){
    	for($i =1; $i < count($arr); $i++){
    		$insertVal = $arr[$i];
    		$insertIndex = $i -1;
    		while($insertIndex >=0&&$insertVal < $arr[$insertIndex]){
    			$arr[$insertIndex+1] = $arr[$insertIndex];
    			$insertIndex--;
    		}
    		$arr[$insertIndex+1] = $insertVal;
    	}
    	return $arr;
    }

    function quickSort($arr){
    	$count = count($arr);
    	if($count <= 1){
    		return $arr;
    	}
    	$base = $arr[0];
    	$leftArr = array();
    	$rightArr = array();
    	for($i=0;$i<$count;$i++){
    		if($arr[$i] < $base){
    			$leftArr[] = $arr[$i];
    		}else{
    			$rightArr = $arr[$i];
    		}
    	}
    	$leftArr = quickSort($leftArr);
    	$rightArr = quickSort($rightArr);
    	return array_merge($leftArr,$base,$rightArr);
    }
