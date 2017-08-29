function getsubCategory($category_array)
 {
     global $category_array,$women_array,$men_array,$kids_array,$digital_array,$sports_array;
     foreach ($category_array as $key => $value)
      {
       # code...
        if($category_array[$key]['category_name']=="Men")
        {
          $men_subcat=$category_array[$key]['id'];
            matchwithparentIdMen($men_subcat,$category_array);
        }
        else if($category_array[$key]['category_name']=="Women")
        {
            $women_subcat=$category_array[$key]['id'];
            matchwithparentIdWomen($women_subcat,$category_array)
        }
        else if($category_array[$key]['category_name']=="Kids")
        {
               $kids_subcat=$category_array[$key]['id'];
            matchwithparentIdWomen($kids_subcat,$category_array)
        }
         else if($category_array[$key]['category_name']=="Digital")
        {
               $digital_subcat=$category_array[$key]['id'];
            matchwithparentIdWomen($digital_subcat,$category_array)
        }
         else if($category_array[$key]['category_name']=="Sports")
        {
               $sports_subcat=$category_array[$key]['id'];
            matchwithparentIdWomen($sports_subcat,$category_array)
        }
     }
    // print_r($category_array);
 }
 function matchwithparentIdMen($men_subcat,$category_array)
 {
   global $men_array;
 foreach ($category_array as $key => $value) 
 {
    if($category_array[$key]['parent_id']==$men_subcat)
        {
            array_push($men_array,array("id"=>$category_array[$key]['id'],"category_name"=>$category_array[$key]['category_name'],"parent_id"=>$category_array[$key]['parent_id']));
            echo 123;
        }
 }
 }
 function matchwithparentIdWomen($women_subcat,$category_array)
 {
   global $womenmen_array;
 foreach ($category_array as $key => $value) 
 {
    if($category_array[$key]['parent_id']==$men_subcat)
        {
            array_push($womenmen_array,array("id"=>$category_array[$key]['id'],"category_name"=>$category_array[$key]['category_name'],"parent_id"=>$category_array[$key]['parent_id']));
        }
 }
 }
 function matchwithparentIdKids($kids_subcat,$category_array)
 {
   global $kids_array;
 foreach ($category_array as $key => $value) 
 {
    if($category_array[$key]['parent_id']==$men_subcat)
        {
            array_push($kids_array,array("id"=>$category_array[$key]['id'],"category_name"=>$category_array[$key]['category_name'],"parent_id"=>$category_array[$key]['parent_id']));
        }
 }
 }
 function matchwithparentIdDigital($digital_subcat,$category_array)
 {
   global $digital_array;
 foreach ($category_array as $key => $value) 
 {
    if($category_array[$key]['parent_id']==$men_subcat)
        {
            array_push($digital_array,array("id"=>$category_array[$key]['id'],"category_name"=>$category_array[$key]['category_name'],"parent_id"=>$category_array[$key]['parent_id']));
        }
 }
 }
 function matchwithparentIdWomen($sports_subcat,$category_array)
 {
  global $sports_array;
    foreach ($category_array as $key => $value) 
 {
    if($category_array[$key]['parent_id']==$sports_subcat)
        {
            array_push($sports_array,array("id"=>$category_array[$key]['id'],"category_name"=>$category_array[$key]['category_name'],"parent_id"=>$category_array[$key]['parent_id']));
        }
 }
 }
 






 if($category_array[$key]['category_name']=="Women")
        {
            $women_subcat=$category_array[$key]['id'];
            echo $women_subcat;
          //matchwithparentIdWomen($women_subcat,$category_array);
        }
         if($category_array[$key]['category_name']=="Kids")
        {
               $kids_subcat=$category_array[$key]['id'];
              echo $kids_subcat;
            //matchwithparentIdWomen($kids_subcat,$category_array)
        }
          if($category_array[$key]['category_name']=="Digital")
        {
               $digital_subcat=$category_array[$key]['id'];
              echo $digital_subcat;
            //matchwithparentIdWomen($digital_subcat,$category_array)
        }
          if($category_array[$key]['category_name']=="Sports")
        {
               $sports_subcat=$category_array[$key]['id'];
              echo $sports_subcat;
            //matchwithparentIdWomen($sports_subcat,$category_array)
        }
     }function getsubCategory($category_array)
 {
     global $women_array,$men_array,$kids_array,$digital_array,$sports_array;
     foreach ($category_array as $key => $value)
      {
       # code...
        if($category_array[$key]['category_name']=="Men")
        {
          $men_subcat=$category_array[$key]['id'];
          echo $men_subcat;
            matchwithparentIdMen($men_subcat,$category_array);
        }
         
     }
    
 }
 function matchwithparentIdMen($men_subcat,$category_array)
 {
  global $men_array;
    foreach ($category_array as $key => $value)
      {
       # code...
        if($category_array[$key]['parent_id']==$men_subcat)
        {
          array_push($men_array, array("name_m"=>$category_array[$key]['category_name']));
        }
 }
 //print_r($men_array);
}