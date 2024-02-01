jQuery( function ( $ ) {
  init_toyyibpay_meta();
  $(".toyyibpay_customize_toyyibpay_donations input:radio").on("change", function() {
    init_toyyibpay_meta();
  });

  function init_toyyibpay_meta(){
    if ($(".toyyibpay_customize_toyyibpay_donations input:radio:checked").val() == "enabled"){
      $(".give_title_toyyibpay2").show();
      $(".toyyibpay_userSecretKey").show();
      $(".toyyibpay_categorycode").show();
      $(".toyyibpay_contact").show();
      $(".toyyibpay_description").show();
      $(".toyyibpay_paymentchannel").show();
      $(".toyyibpay_paymentcharge").show();
      $(".toyyibpay_extraemail").show();
      $(".give_title_toyyibpay2").show();
      $(".toyyibpay_userSecretKeyDev").show();
      $(".toyyibpay_categorycodeDev").show();
    } else {
      $(".give_title_toyyibpay2").hide();
      $(".toyyibpay_userSecretKey").hide();
      $(".toyyibpay_categorycode").hide();
      $(".toyyibpay_contact").hide();
      $(".toyyibpay_description").hide();
      $(".toyyibpay_paymentchannel").hide();
      $(".toyyibpay_paymentcharge").hide();
      $(".toyyibpay_extraemail").hide();
      $(".give_title_toyyibpay2").hide();
      $(".toyyibpay_userSecretKeyDev").hide();
      $(".toyyibpay_categorycodeDev").hide();
    }
  }
});