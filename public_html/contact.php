<?php
include 'titles.php';
?>
<?php
include 'nav2.php';
?>
<body>
    <div>
        <img src="img/contactimage.jpg" width="100%" class="img-responsive">
    </div>
<!--<section class="bg-primary2" id="sec2" >
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 text-center">
                        <p class="section-heading" style="font-size: 17px">Thanks for your interest in the Eco-stove. We are eager 
                            to hear from you and appreciate your inquiries.  Feel free to walk 
                            into any of our branch for further inquiry.
                            Please feel free to contact us as well any time any day..</p>
                        <h2 class="section-heading">Watch the Difference</h2>
                        <hr class="light">
                    </div>
                </div>
            </div>
        </section>-->

    <div class="container-fluid" style="background-color:#040404;color: white">
        <br />
        <div class="col-md-4">
            <div style="text-align: center; font-size:15px">
                <p style="color:greenyellow" class="text-uppercase">Reach us</p>
                <!--<p style="color:  #990000">Chief Executive Officer & CTO</p>-->
                <p class=" lead text-justify" style="font-size:16px">
                    Thanks for your interest in the Eco-stove. We are eager 
                    to hear from you and appreciate your inquiries.  Feel free to walk 
                    into any of our branch for further inquiry.
                    Please feel free to contact us as well any time any day.
                </p>
            </div>
        </div>

        <div class="col-md-4"> 
            <div style="text-align: center; font-size:15px">
                <p style="color: orangered" class="text-uppercase">general Offices.</p>

                <p class="lead text-justify" style="font-size:16px">
                    5 BLACK HORSE LANE.
                    <br />
                    WALTHAM FOREST BUSINESS CENTRE.
                    <br />
                    LONDON, E17 6DS.
                    <br />
                    ENGLAND.
                <p>

                <p class="lead text-justify" style="font-size:16px">

                    ECO-GROUP LTD
                    <br />
                    1078 Ertec Lane, off Mbuubi Road,.
                    <br />
                    Wakaliga Rd, Lungujja, Mengo.
                    <br />
                    P.O Box 217 Kampala,
                    <br />
                    Uganda, East Africa
                    <br />
                    Office; +256200905501,+256702920729

                </p>
                <p class="lead text-justify" style="font-size:16px">
                    Branch office;
                    <br />
                    Plot 87 Kiira Road, Kamwokya, Kampala;
                    <br />
                    +256200905501 (land line).
                    <br />
                    +256702920729/+256776920729;
                    <br />

                </p>

                <p class="lead text-justify" style="font-size:16px">
                    Wakaliga Branch;
                    <br />
                    Wakaliga Road-After Lubiri Secondary school/FUFA House)
                    <br />
                    Mengo, Kampala, Uganda.
                    <br />
                    East Africa;
                    <br />

                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div style="text-align: center; font-size:15px">
                <p style="color:#0e9ec7" class="text-uppercase">mail us</p>


                               <div class="panel panel-default">
                                        <div class="panel-heading" style="color:#1d4608" id="adcontact"> <strong>Send Us a Mail.</strong></div>
                                        <div class="panel-body">
                                            <form method="post" action="mailFunction.php" role="form">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" class="form-control" name="name" placeholder="Enter Name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email Address</label>
                                                    <input type="email" class="form-control" name="email" placeholder="Enter Email">
                                                </div>
                                                <div class="form-group">
                                                    <label for="subject">Subject</label>
                                                    <input type="text" class="form-control" name="subject" placeholder="Enter Subject">
                                                </div>
                                                <div class="form-group">
                                                    <label for="message">Message</label>
                                                    <textarea class="form-control" name="message" rows="7"></textarea>
                                                </div>
                                                <input type="submit" class="btn btn-default" value="Send Mail" id="cbtn" />
                                            </form>
                                        </div>
                                    </div>
                
            </div>
        </div>
    </div>
</div>
<div>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script><div style="overflow:hidden;height:400px;width:1300px;"><div id="gmap_canvas" style="height:400px;width:100%;">
            <style>#gmap_canvas img{max-width:none!important;background:none!important}</style><a class="google-map-code" href="http://www.zahnarzt-rosenheim.org" id="get-map-data">Eco Stove Innovations</a></div></div>
    <script type="text/javascript"> function init_map(){var myOptions = {zoom:14, center:new google.maps.LatLng(0.3399778000000001, 32.58709010000007), mapTypeId: google.maps.MapTypeId.ROADMAP}; map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
        marker = new google.maps.Marker({map: map, position: new google.maps.LatLng(0.3399778000000001, 32.58709010000007)}); infowindow = new google.maps.InfoWindow({content:"<b>Eco Stove Innovations</b><br/>kira road kamwokya<br/> KAMPALA" }); google.maps.event.addListener(marker, "click", function(){infowindow.open(map, marker); }); infowindow.open(map, marker); }google.maps.event.addDomListener(window, 'load', init_map);</script>
</div>

<section id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 text-center">
                <h2 class="section-heading">Let's Get In Touch!</h2>
                <hr class="primary">
                <p>Ready to own an Eco stove? That's great! Give us a call or send us an email and we will get back to you as soon as possible!</p>
            </div>
            <div class="col-lg-4 col-lg-offset-2 text-center">
                <i class="fa fa-phone fa-3x wow bounceIn"></i>
                <p>+256 702 920 729</p>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fa fa-envelope-o fa-3x wow bounceIn" data-wow-delay=".1s"></i>
                <p><a href="info@ecostoveinnovations.com">info@ecostoveinnovations.com</a></p>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>
</body>
</html>
