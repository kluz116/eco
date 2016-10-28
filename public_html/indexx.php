<!DOCTYPE html>
<!--
The ark computer programers.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <link href="css/style.css" rel="stylesheet">
        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <meta property="og:url"           content="http://www.ecostoveinnovations.com/index.php" />
	<meta property="og:type"          content="website" />
	<meta property="og:title"         content="Your Website Title" />
	<meta property="og:description"   content="Your description" />
	<meta property="og:image"         content="http://www.your-domain.com/path/image.jpg" />
        <!-- Custom CSS -->
        <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
        <title>ecostove innovations</title>
        <script>
            $(document).ready(function () {
                $("#navToggle a").click(function (e) {
                    e.preventDefault();

                    $("header > nav").slideToggle();
                    $("#logo").toggleClass("menuUp menuDown");
                });

                $(window).resize(function () {
                    if ($(window).width() >= "600") {
                        $("header > nav").css("display", "block");

                        if ($("#logo").attr('class') == "menuDown") {
                            $("#logo").toggleClass("menuUp menuDown");
                        }
                    }
                    else {
                        $("header > nav").css("display", "none");
                    }
                });

                $("header > nav > ul > li > a").click(function (e) {
                    if ($(window).width() <= "600") {
                        if ($(this).siblings().size() > 0) {
                            e.preventDefault();
                            $(this).siblings().slideToggle("fast")
                            $(this).children(".toggle").html($(this).children(".toggle").html() == 'close' ? 'expand' : 'close');
                        }
                    }
                });
            });

        </script>
    </head>
    <body style="background-color: #118C4E">
        <div id="wraper">
            <?php
            include 'dependencies.php';
            include 'header.php';
            ?>
            <div id="page-wrap">

            <!-- setting up the slide show-->
            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="margin: 0px">
                <!--Indicators-->
                <ol class="carousel-indicators">
                    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="2"></li>

                </ol>

                <!--Wrapper for slides-->
                <div class="carousel-inner" role="listbox">
                    <div class="item active">
                        <img src="img/group.jpg" width="100%" alt="beta">
                        <div class="carousel-caption">
                            <a href="systems.php" style="text-decoration: none; color: whitesmoke"><h1 class="moving">Clean Cooking For Every Household.</h1></a>
                            <!--<p>shall we ever be like this</p>-->
                        </div>
                    </div>
                    <div class="item">
                        <img src="img/group.jpg" width="100%" alt="...">
                        <div class="carousel-caption">
                            <a href="systems.php" style="text-decoration: none; color: whitesmoke"><h1 class="moving" >We Believe The Sun can Cooking.</h1></a>
                        </div>

                    </div>
                    <div class="item">
                        <img src="img/group.jpg" width="100%" alt="...">
                        <div class="carousel-caption">
                            <a href="web.php" style="text-decoration: none; color: whitesmoke"><h1 class="moving">Reduce Your Expenditure on charcoal and Firewood Fuels.</h1></a>
                        </div>
                    </div>

                </div>
                <!-- Controls-->
                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
            <div class="jumbotron" style="background-color: yellowgreen;margin-bottom: auto">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6 col-md-10">
                            <h2 style="font-size: 60px;">Performance Beyond Compare...</h2>
                            <br/>
                            <p class="lead text-justify" style="font-size:17px;">It cooks, it Lights your home, It charges phones, Plays inbuilt Fm Radio/MP3, It Irons- provides immediate environmental, economic, health and social benefits. A Cook stove with ZERO running cost and ZERO Smoke! every kitchen must have

And whats more; Its made in various sizes to meet users needs, some portable, all Neat, safe, up to 2 years re-usable stones and the briquettes used as fire starters can be made from your back-yard.  .</p>
                            <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <img src="img/image.jpg" style="width: 100%">
            </div>
            <br />
            <br />
            <div class="container-fluid" style="background-color: #000000;padding-top: 20px;margin-top: auto">
                <div class="col-sm-6 col-md-4">
                    <div class="thumbnail">
                        <img src="img/group.jpg" alt="...">
                        <div class="caption">
                            <a href="products.php" style="text-decoration: none; text-align: center"><h3>See How Ecostove is Changing Peoples' lives.</h3></a>
                            <!--<p>...</p>-->
                            <p style="text-align: center"><a href="products.php" class="btn btn-primary btn-lg btn-success" role="button">Learn More</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="thumbnail">
                        <img src="img/food.jpg" alt="...">
                        <div class="caption">
                            <a href="products.php" style="text-decoration: none; text-align: center"> <h3>See How Ecostove is Tranforming Businesses.</h3></a>
                            <!--<p>...</p>-->
                            <p style="text-align: center"><a href="products.php" class="btn btn-primary btn-lg btn-success" role="button">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="thumbnail">
                        <img src="img/prep.jpg" alt="...">
                        <div class="caption">
                            <a href="services.php" style="text-decoration: none; text-align: center"><h3>The Eco Pre-pay a Must Have For Every Household. .</h3></a>
                            <!--<p>...</p>-->
                            <p style="text-align: center"><a href="services.php" class="btn btn-primary btn-lg btn-success" role="button">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            include 'footer.php';
            ?>
        </div>
        </div>
    </body>
</html>
