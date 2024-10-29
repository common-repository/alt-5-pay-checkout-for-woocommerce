<?php

class ALT5_Sec
{
    public function ALT5_checkSecurity($signature,$secretkey,$post)
{

    $hmac=hash_hmac('sha512', $post, $secretkey);
   

   if($hmac==$signature){

    return true;
   }else{
    return false;
   }

    

}

}