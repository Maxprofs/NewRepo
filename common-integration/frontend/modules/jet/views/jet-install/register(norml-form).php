<?php
use frontend\modules\jet\components\Data;
?>
<div class="top_error alert-danger" style="display:none; border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
<div class="top_success alert-success" style="display:none; border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
<div class="site-register">
    <div class="content-section">
        <div class="">
            <form method="post" action="/shopify/jet-install/renderstep" id="jetregistration-form">
                <input type="hidden" value="VlJHeUctT18fYhY6DB84JQMQGAssHho9HxUVIyEdHG4Zaj5KJR8GLg==" name="_frontendCSRF">

                <!-- <div data-error="You should have Jet Seller Account." class="jetregistration-seller_account">
                    <label for="seller_account" class="control-label">Have Seller Account</label>
                    <input type="checkbox" name="JetRegistration[seller_account]" class="form-control required" id="seller_account">
                </div> -->

                <div data-error="Full Name cannot be blank." class="jetregistration-name">
                    <span class="required-icon">*<span>
                    <label for="name" class="control-label">Full Name</label>
                    <input type="text" name="JetRegistration[name]" class="form-control required" id="name">
                </div>

                <div data-error="Mobile cannot be blank." class="jetregistration-mobile">
                    <label for="mobile" class="control-label">Mobile</label>
                    <input type="text" name="JetRegistration[mobile]" class="form-control" id="mobile">
                </div>

                <div data-error="Email cannot be blank." class="jetregistration-email">
                    <span class="required-icon">*<span>
                    <label for="email" class="control-label">Email</label>
                    <input type="text" name="JetRegistration[email]" class="form-control required" id="email">
                </div>

                <!-- <div data-error="Plan Type is Required" class="jetregistration-plan_type">
                    <label for="jetregistration-plan_type" class="control-label">Plan Type</label>
                    <select name="JetRegistration[plan_type]" class="form-control" id="jetregistration-plan_type">
                    <option value="">Choose...</option>
                    <option value="yearly">yearly($299/year)</option>
                    <option value="monthly">monthly($30/month)</option>
                    </select>
                </div> -->

                <div class="jetregistration-shipping_source">
                    <span class="required-icon">*<span>
                    <label for="shipping_source" class="control-label">Shipping Source</label>
                    <div id="shipping_source">
                        <label>
                            <input type="checkbox" value="FBA" name="JetRegistration[shipping_source][]" class=""> FBA</label>
                        <label>
                            <input type="checkbox" value="Shipwork" name="JetRegistration[shipping_source][]" class=""> 
                            Shipwork
                        </label>
                        <label>
                            <input type="checkbox" value="shipstation" name="JetRegistration[shipping_source][]" class="">
                            Shipstation
                        </label>
                        <label>
                            <input type="checkbox" value="Other" name="JetRegistration[shipping_source][]" class="">
                            Other
                        </label>
                    </div>
                </div>

                <!-- <div class="jetregistration-product_count">
                    <label for="product_count" class="control-label">Product Count</label>
                    <select name="JetRegistration[product_count]" class="form-control" id="product_count">
                        <option value="">Choose...</option>
                        <option value="1000">0<1000</option>
                        <option value="5000">1000<5000</option>
                        <option value="10000">5000<10000</option>
                        <option value="50000">10000<50000</option>
                    </select>
                </div> -->

                <div class="jetregistration-reference">
                    <label for="reference" class="control-label">Where did you here about us.</label>
                    <select name="JetRegistration[reference]" class="form-control" id="reference">
                        <option value="">Choose...</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Google">Google</option>
                        <option value="Yahoo">Yahoo</option>
                        <option value="LinkedIn">LinkedIn</option>
                        <option value="YouTube">YouTube</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div data-error="This cannot be blank." style="display:none;" class="jetregistration-other_reference">
                    <label for="other_reference" class="control-label"></label>
                    <textarea name="JetRegistration[other_reference]" class="form-control required" disabled="disabled" id="other_reference"></textarea>
                </div>

                <label class="control-label">Terms and conditions</label>
                <div class="terms-n-condition">   
                    <strong>CedCommerce Terms & Conditions and Privacy Policy (JET Shopify)</strong><br><br>
                    <p>
                    CedCommerce, LLC currently provides Jet.com integration functionality with integration into other 
                    marketplaces. Our goal is to provide reliable service for a competitive price that helps merchants sell 
                    their products across multiple platforms. We work hard to ensure this service works at its full potential
                    and meet each case with the utmost dedication.<br><br>
                    We understand how important a secure website is to e-commerce. Our investment in infrastructure 
                    and security only highlights this commitment. Unfortunately, our service is reliant on 3rd party 
                    providers that may have other measures in place. Therefore, we cannot guarantee a 100% error free 
                    code or website that is accessible at all times. Our service is not liable for any damages or loss of 
                    income, revenue or data. Additionally, there is no guarantee that the error in code or functionality will 
                    prevent the suspension or deletion of a Jet.com account.<br><br>
                    As a user of CedCommerce.com, products you are responsible for your account as well as any content
                    related to your account. Furthermore, you agree to use CedCommerce.com in compliance with all 
                    applicable laws and will in no way violate the Jet.com Terms of Use, API Terms of Use, or Seller 
                    Agreement. <br><br>
                    Furthermore, CedCommerce do not hold responsibility for any illegal use and reserve the right to 
                    close an account for any reason.<br><br>
                    CedCommerce, LLC (“the Service”) offering use of the Service is expressly conditioned on your 
                    acceptance of these CedCommerce.com Terms and Conditions. By using the Service, you signify that 
                    you unconditionally agree and accept to be legally bound by these Terms and Conditions. No other 
                    terms or conditions of any sort in any document, writing or other communication whatsoever made by
                    you to CedCommerce.com or its employees, representatives or agents in relation to the Service shall 
                    be applicable to or binding on CedCommerce.com.<br><br>
                    CedCommerce.com is not associated in any means with served marketplaces, and the Services 
                    provided are designed to let users to communicate with those marketplaces via available API in a 
                    manner that follows all terms and conditions of those marketplaces. This also means that 
                    CedCommerce will not be responsible for the eventual termination of seller’s account on any 
                    marketplaces, and fees charged by those marketplaces, etc.<br><br>
                    <strong>Updates</strong><br><br>
                    We may change these Terms of Use from time to time, by posting updates to our website. An update 
                    will be effective for any website use after the date of the update. We encourage you to review our 
                    Terms of Use from time to time for possible changes. Your use of our website after an update 
                    constitutes your agreement to the update.<br><br>
                    <strong>Payment Terms</strong><br><br>
                    Cedcommerce reserves right, and would charge you with 15 $/ Hour as a customisation charge for the 
                    additional on demand benefits /requests made from your end for a improving or enhancing the 
                    functionality.<br><br>
                    <strong>Subscriptions</strong><br><br>
                    Some of CedCommerce Services are billed on a subscription basis means that you will be billed in 
                    advance on a recurring, periodic basis (each period is called a “billing cycle”). Billing cycles are 
                    typically two weeks, monthly or annual, depending on what subscription plan you were offered. Your 
                    Subscription will automatically renew at the end of each billing cycle unless you cancel auto-renewal 
                    through your online account management page, or by contacting our customer support team. While 
                    we will be sad to see you go, you may cancel auto-renewal on your Subscription at any time, in which
                    case your Subscription will continue until the end of that billing cycle before terminating. You may 
                    cancel auto-renewal on your Subscription immediately after the Subscription starts if you do not want 
                    it to renew.<br><br>
                    We may change the subscription fee charged for the Services at any time. The change will become 
                    effective only at the end of the then-current billing cycle of your subscription.<br><br>
                    <strong>Refunds</strong><br><br>
                    You are responsible for keeping your billing information up to date and your account current. You will
                    not be liable for any sort of partial or prorated refund of your subscription fee for any time during 
                    which you do not use the Services. The refund would not be applicable to the boundaries beyond the 
                    operational field and the issues/concerns/liabilities not under the CedCommerce service policies and 
                    norms.<br><br>
                    <strong>Usage of the Service</strong><br><br>
                    You are responsible for your account, content, and communications with others while using the 
                    Services. You agree to use the Services in compliance with applicable law. This is your responsibility 
                    and you agree to not use CedCommerce in a way that violates JET.com Terms of Use 
                    (https://jet.com/terms-of-use) , it’s API Terms of Use (https://developer.jet.com/tos) or agreemenst 
                    with any other marketplace. By using CedCommerce.com, you agree to not violate any of 
                    marketplace terms. In the event of violating any marketplace rules, we have the right to close your 
                    account. We are also not responsible for any illegal use of CedCommerce.com by our users.<br><br>
                    <strong>Account Termination</strong><br><br>
                    CedCommerce.com has the right to terminate its relationship with you, without prior notice, if you 
                    breach or fail to comply with any provision of these Terms and Conditions. This Agreement and your 
                    ability to use the Service shall also automatically terminate upon the expiry of your subscription.
                    CedCommerce.com has no responsibility to maintain your user account or any of your data after 
                    termination. CedCommerce.com is not responsible for any fees, damages, or claims you may suffer in
                    relation to the same or any claims or actions you may have as a result of termination.<br><br>
                    If termination is due to your failure to renew your subscription on a timely basis, you may 
                    reactivate your account and access your data if you renew your subscription with CedCommerce.com.
                    To do so you may have to pay any subscription fees or charges that may be imposed by 
                    CedCommerce.com, including reactivation fees (if any), within any period that may be stated by 
                    CedCommerce.com. Failure to pay reactivation fees may result in your user account being deleted, 
                    and permanent removal from its systems.<br><br>
                    <strong>Your Data</strong><br><br>
                    You or your customers, partners or associates – whichever may be applicable – are the owner of any
                    data you upload into the Service (“Your Data”). By “Your Data,” we mean images of your products, 
                    your personal and company information, products, customers and sales information. You hereby agree
                    that CedCommerce.com is the owner of all data other than Your Data, including any system generated
                    data generated by the Service or any data compiled from data inputted into the Service by all users of 
                    the Service on an aggregate basis (“CedCommerce.com’s Data”). CedCommerce.com may use all of 
                    CedCommerce.com’s Data in any way it chooses (including to improve or adapt its services), or to 
                    create or design new products and services. Data other than "Your Data," would be statistics of 
                    amount of sales per day across accounts of all of our customers, which we can use for example to 
                    determine our needs in server’s capacity and speed, we own these global statistics (which do not 
                    contain any information that can be clarified as "Your Data").<br><br>
                    You acknowledge and agree that the nature of the Internet is international and that 
                    CedCommerce.com has your express consent to store and provide access to your personal or 
                    confidential information, and that of your users(s) and customers, and to transmit and deliver such 
                    information via the Internet (which may involve its transmission across multiple jurisdictions).<br><br>
                    You are responsible for all data on your account and you agree to comply with all applicable legal 
                    requirements for the sale, transfer and transport of an item, including but not limited to statutes, 
                    regulations or requirements of any country, state, locality, province, municipality or other government 
                    authority or regulatory entity regarding sales or auctions, the sale and/or transfer of any Item 
                    (including firearms, ammunition, black powder, or any other item), export or import control, taxation, 
                    duties or tariffs, presence or licensing of brokers (the foregoing, “Legal Requirements”) governing the
                    specific requirements for transfer and shipping of firearms. CedCommerce.com is not responsible for 
                    the seller’s products, nor is liable for any legal actions that may result from the sale of the seller’s 
                    products.<br><br>
                    <strong>Your Account and Password</strong><br><br>
                    It is your responsibility to keep secure and confidential any password(s) and user ID(s) 
                    CedCommerce.com may issue to you to access the Service, rests solely with you and the employees, 
                    representatives and agents of yours that are entrusted with the same. If you become aware of any 
                    unauthorized access to your account(s), or any misuse of your password(s) and user ID(s), you must 
                    follow the CedCommerce process to disable your account(s) and/or re-issue new password(s) or user 
                    ID(s) as soon as possible.<br><br>
                    You agree that you are solely responsible for the actions and omissions of the person(s) you 
                    nominate as user(s) or administrator(s) of your account(s) for the Service. You also agree that 
                    CedCommerce.com may accept instructions and requests from, and communicate with such person(s) 
                    until and unless CedCommerce.com receives notification that such person(s) that this is no longer in 
                    effect. At this point, all requests and communications are valid and legally binding on you. In 
                    addition, you are responsible for any and all use of your account(s) by any persons who are in 
                    possession of your user ID(s) or password(s).<br><br>
                    <strong>No Warranties</strong><br><br>
                    UNLESS EXPRESSLY PROVIDED HEREIN, TO THE FULLEST EXTENT PERMITTED BY 
                    LAW, CedCommerce.com MAKES NO WARRANTY OR REPRESENTATION OF ANY KIND 
                    REGARDING ITS WEBSITE, THE SERVICE, THE PRODUCTS OR SERVICES AVAILABLE ON 
                    THIS WEBSITE AND/OR ANY MATERIALS PROVIDED ON THIS WEBSITE, ALL OF WHICH 
                    ARE PROVIDED ON AN “AS IS WHERE IS” BASIS. CedCommerce.com DOES NOT WARRANT
                    THE ACCURACY, COMPLETENESS, CURRENCY OR RELIABILITY OF ANY OF THE 
                    CONTENT OR DATA FOUND ON THE SERVICE, OR THIS WEBSITE. CedCommerce.com 
                    EXPRESSLY DISCLAIMS ALL WARRANTIES, AND TERMS AND CONDITIONS IN 
                    RELATION TO THE SERVICE, INCLUDING ALL IMPLIED WARRANTIES AS TO 
                    MERCHANTABILITY, SATISFACTORY QUALITY, FITNESS FOR A GENERAL OR 
                    PARTICULAR PURPOSE AND NON-INFRINGEMENT OF PROPRIETARY RIGHTS, AND 
                    THOSE ARISING BY STATUTE OR OTHERWISE IN LAW OR FROM A COURSE OF DEALING
                    OR USAGE OF TRADE TO THE FULLEST EXTENT PERMITTED BY THE LAWS OF NEW 
                    YORK and United States of America.<br><br>
                    CedCommerce.com DOES NOT WARRANT THAT THE SERVICE, THIS WEBSITE, ITS 
                    SERVERS OR ANY E-MAIL SENT FROM CedCommerce.com IS FREE OF VIRUSES OR OTHER
                    HARMFUL COMPONENTS.<br><br>
                    FOR THE AVOIDANCE OF DOUBT, CedCommerce.com DOES NOT GUARANTEE THE 
                    EFFECTIVENESS OF THE SERVICE. We cannot also guarantee that errors in the code or 
                    functionality will not cause your account to be suspended or deleted by JET.com.<br><br>
                    CedCommerce.com TAKES NO RESPONSIBILITY FOR THE SECURITY, CONFIDENTIALITY 
                    OR PRIVACY OF THE COMMUNICATIONS AND/OR DATA TRANSMITTED OVER THE 
                    INTERNET AND DOES NOT WARRANT (AND EXPRESSLY EXCLUDES ANY AND ALL 
                    EXPRESS OR IMPLIED WARRANTIES) THAT THE SERVICE WILL BE WITHOUT FAILURE, 
                    DELAY, INTERRUPTION, ERROR OR LOSS OF CONTENT, DATA OR INFORMATION. IN 
                    ADDITION, CedCommerce.com SHALL NOT BE LIABLE FOR ANY COMPATIBILITY ISSUES 
                    PERTAINING TO CUSTOMERS’ COMPUTERS, APPLICATIONS OR OTHER SOFTWARE ON 
                    ANY COMPUTERS USING THE SERVICE.<br><br>
                    <strong>Limitation of Liability</strong><br><br>
                    TO THE FULLEST EXTENT PERMITTED BY LAW IN NO EVENT SHALL CedCommerce.com 
                    BE LIABLE FOR ANY INJURY, LOSS, CLAIM, DAMAGE, OR ANY SPECIAL, EXEMPLARY, 
                    PUNITIVE, INDIRECT, INCIDENTAL OR CONSEQUENTIAL DAMAGES OF ANY KIND OR 
                    FOR ANY LOST PROFITS OR LOST SAVINGS, WHETHER BASED IN CONTRACT, TORT 
                    (INCLUDING NEGLIGENCE),<br><br>
                    EQUITY, STRICT LIABILITY, STATUTE OR OTHERWISE, WHICH ARISES OUT OF OR IS IN 
                    ANY WAY CONN CONTENT FOUND HEREIN, (II) ANY FAILURE OR DELAY (INCLUDING, 
                    BUT NOT LIMITED TO THE USE OF OR INABILITY TO USE ANY COMPONENT OF THE 
                    SERVICE OR THIS SITE), OR (III) THE PERFORMANCE OR NON PERFORMANCE BY 
                    CedCommerce.com EVEN IF THE CedCommerce.com HAS BEEN ADVISED OF THE 
                    POSSIBILITY OF DAMAGES TO SUCH PARTIES OR ANY OTHER PARTY.<br><br>
                    <strong>Force Majeure</strong><br><br>
                    CedCommerce.com has no responsibility for and is released from all contractual obligations and 
                    liability (e.g. for damages) if its performance of these Terms and Conditions is affected by an event of 
                    force majeure. For the purpose of this clause, the term “force majeure” means and includes any event 
                    which was not under the control of CedCommerce.com, or was not reasonably foreseeable, including, 
                    but not limited to any natural disaster such as thunderstorm, flood or storm, fire, national emergency, 
                    strike or equivalent labor action, or the unavailability of the Internet for reasons beyond the control of 
                    CedCommerce.com.<br><br>
                    <strong>Jurisdiction</strong><br><br>
                    This Agreement shall be construed and governed in accordance with the laws of the India, and the 
                    parties hereto submit to the exclusive jurisdiction of the courts of the India.
                    Thank you for taking the time to understand our Terms & Conditions.
                    Any questions regarding the Terms and Condition should be addressed to : 
                    shopify@cedcommerce.com </p>
                </div>

                <a target="_blank" href="/shopify/jet-policy/Jet Shopify Policy Document .pdf" class="btn btn-primary">View & Download</a>

                <div data-error="You must agree to the terms and conditions." class="jetregistration-agreement">
                    <input type="checkbox" value="1" name="JetRegistration[agreement]" class="required" id="agreement">
                    <labelf for="agreement">I Accept Terms & Conditions</label>
                </div>

                <div class="form-group">
                    <button class="next btn btn-primary" type="button">Next</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    var url = "<?=  Data::getUrl('jet-register/save') ?>";
    $('.next').on('click', function(event) {
        removeErrorMessage();
        var validate1 = validateFormFields('input');
        var validate2 = validateFormFields('select');
        var validate3 = validateFormFields('textarea');
        var validate4 = validateFieldValues();
        var validate5 = validateShippingSource();

        /*console.log(validate1);
        console.log(validate2);
        console.log(validate3);
        console.log(validate4);
        console.log(validate5);*/
        if(validate1 && validate2 && validate3 && validate4 && validate5) {
            $('#LoadingMSG').show(); 
            $.ajax({
                method: "POST",
                url: url,
                data: $("form").serialize(),
                dataType : "json"
           })
           .done(function(response) {
                $('#LoadingMSG').hide();
                if(response.success) {
                     $('.top_success').html(response.message);
                     $('.top_success').show();
                     nextStep();
                } else {
                     saveFlag = false;
                     $('.top_error').html(response.message);
                     $('.top_error').show();
                }
           });
        }
    });
});

function validateFormFields(inputTag)
{
    var validate = true;
    $('#jetregistration-form '+inputTag+'.required').each(function(){
        var type = $(this).attr('type');
        var isDisabled = $(this).prop('disabled');
        if(type != 'hidden' && !isDisabled)
        {
            if(type == 'checkbox') {
                var checked = $(this).is(":checked");
                if(checked == false) {
                    var error_message = $(this).parent('div').attr('data-error');
                    if(error_message != 'undefined') {                        
                        var element = $(this).parent('div');
                        addErrorMessage(element, error_message);
                        validate = false;
                    }
                }
            } else {
                if($(this).val() == "") {
                    var error_message = $(this).parent('div').attr('data-error');
                    if(error_message != 'undefined') {
                        var element = $(this).parent('div');
                        addErrorMessage(element, error_message);
                        validate = false;
                    }
                }
            }
        }
    });
    return validate;
}

function validateFieldValues()
{
    var validate = true;

    //validate mobile number
    var mobile = $("#mobile").val().trim();
    if(mobile != '' && !validatePhone(mobile))
    {
        var error_message = "Invalid Mobile No.";
        var element = $("#mobile").parent('div');
        addErrorMessage(element, error_message);
        validate = false;
    }

    //validate email
    var email = $('#email').val().trim();
    if(email != '' && !validateEmail(email))
    {
        var error_message = "Invalid Email.";
        var element = $("#email").parent('div');
        addErrorMessage(element, error_message);
        validate = false;
    }

    return validate;
}

function validatePhone(inputtxt)
{
    var phoneno = /^\d{10}$/;  
    if(inputtxt.match(phoneno)) {
        return true;  
    } else {
        return false;  
    }  
}

function validateEmail(email)
{
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    if(filter.test(email)) {
        return true;
    } else {
        return false;
    }
}

function validateShippingSource()
{
    var validate = false;
    $('#shipping_source input').each(function(){
        var type = $(this).attr('type');
        if(type == 'checkbox')
        {
            var checked = $(this).is(":checked");
            if(checked == true) {
                validate = true;
            }
        }
    });
    if(validate == false)
    {
        var error_message = 'Please Choose Shipping Source.';
        var element = $('.jetregistration-shipping_source');
        addErrorMessage(element, error_message);
    }

    return validate;
}

function addErrorMessage(element, message)
{
    var errorHtml = '<div style="color:#a94442;" class="select_error">'+message+'</div>';
    element.append(errorHtml);
}

function removeErrorMessage()
{
    $('.select_error').remove();
}
</script>

<script>
$(document).ready(function(){
    //introJs().start();

    //hide or show other "hear about us" field.
    $('#reference').change(function() {
        if($(this).val()=='Other') {
            $('.jetregistration-other_reference').show();
            $("#other_reference").prop('disabled', false);
        } else {
            $('.jetregistration-other_reference').hide();
            $('#other_reference').val("");
            $("#other_reference").prop('disabled', true);
        }
    });
 });
</script>
