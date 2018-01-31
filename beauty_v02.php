<?php

function main_func(){
	
	$before_url=crawl_page("https://www.ptt.cc/bbs/Beauty/index.html", 5);
	$final_page=random_page($before_url);
	$after_url=crawl_page("https://www.ptt.cc/bbs/Beauty/index".$final_page.".html", 5);
	$final_url=first_cut($after_url);
	echo "{\"text\": \"https://webptt.com/m.aspx?n=" . $final_url . "\"}";
}


function crawl_page($url, $depth = 5) {
    if($depth > 0) {

		$count=0;
		$top_string="公告";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
    }
}


function first_cut($check_string){
	$count=0;
	$post_count=0;
	$final_count=0;
	$the_num=0;
	$the_next_count=0;
	$top_count=50;
	$top_string="公告";
	preg_match_all("|nrec\"><span(.*?)</a>|s", $check_string, $full_string);  //must need thumb number, or this post is deleted
	foreach($full_string[1] as $key => $final_string){
		
		$final_string=str_replace(array("\r", "\n", "\r\n", "\n\r"), '', $final_string);  //let all new line change to empty
		
		if(!$rtn=strpos($final_string, $top_string)){     //ingnore the top post 
			
			$new_string[$count]=$final_string;    //save strings I need in new array 
			$count=$count+1;
		}
	}
	for($i=0;$i<$count ;$i=$i+1 ) {   //check the post number 
		
		$rtn1=strpos($new_string[$i], "\">");
		$rtn2=strpos($new_string[$i], "</span>");

		$pos=$rtn2-$rtn1;
		
		if($pos>2){
			$post_num=substr($new_string[$i],($rtn1+2),$pos-2);
			if($post_num=="爆"){
				$final_link_string[$post_count]=cut_link($new_string[$i]);  //add hot post to the array
				$post_count=$post_count+1;
			}else{
				$the_num=(int)$post_num;
				if($top_count<$the_num){
					$final_link_string[$post_count]=cut_link($new_string[$i]);  //post num large than 50 add to the array
					$post_count=$post_count+1;
				}else{
					if($the_next_count<$the_num){
						$the_next_count=$the_num;
						$next_link_string=cut_link($new_string[$i]);
					}
				}
			}
		}
	}
	
	if($post_count!=0){
		$final_count=random_post($post_count);
		return $final_link_string[$final_count];
	}else{
		return $next_link_string;
	}
	
	//echo "text : ".$final_link_string."\n<br>";
}

function random_post($the_count){
	$rand_count=0;
	$rand_count=rand(0,($the_count-1));
	
	return $rand_count;
}

function random_page($the_page){
	
	preg_match_all("|最舊.*?index(.*?).html\">.*?上頁|s", $the_page, $page_num);
	
	$total_page_num=(int)$page_num[1][0];    //show the cut item
	$front_page_num=$total_page_num-200;     //random from newest 200 pages
	$rand_page=rand($front_page_num,$total_page_num);
	
	return $rand_page;
}


function cut_link($link_string){
	
	$rtn_front=strpos($link_string, "href=\"");
	$rtn_last=strpos($link_string, "l\">");
	$rtn_total=$rtn_last-$rtn_front;
	$rtn_string=substr($link_string,($rtn_front+7),$rtn_total-5);

	return $rtn_string;
}


function cut_post($check_string){
	preg_match_all('~<div.*?nrec.*?>(.*?^)</span>~', $check_string, $total_num);
	foreach($total_num[1] as $key => $final_num){
		echo "total num : ".$final_num."\n<br>";
	}
}

//crawl_page("https://www.ptt.cc/bbs/Beauty/index.html", 5);
main_func();

?>
