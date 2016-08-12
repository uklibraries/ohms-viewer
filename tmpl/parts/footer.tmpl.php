<div id="footer">
    <div id="footer-metadata">
        <?php if (!empty($rights)) { ?>
            <h3><a href="#" id="lnkRights">View Rights Statement</a></h3>
            <div id="rightsStatement"><p><?php echo $rights; ?></p></div>
        <?php } else { ?>
            <h3>No Rights Statement</h3>
        <?php }    ?>

        <?php if (!empty($usage)) { ?>
            <h3><a href="#" id="lnkUsage">View Usage Statement</a></h3>
            <div id="usageStatement"><p><?php echo $usage; ?></p></div>
        <?php } else { ?>
            <h3>No Usage Statement</h3>
        <?php }    ?>

        <?php if (!empty($collectionLink)) { ?>
            <h3>Collection Link: <a href="<?php echo $interview->collection_link ?>"><?php echo $interview->collection ?></a></h3>
        <?php }    ?>

        <?php if (!empty($seriesLink)) { ?>
            <h3>Series Link: <a href="<?php echo $interview->series_link ?>"><?php echo $interview->series ?></a></h3>
        <?php }    ?>

        <h3>Contact Us: <a href="mailto:<?php echo $contactemail ?>"><?php echo $contactemail ?></a> | <a href="<?php echo $contactlink ?>"><?php echo $contactlink ?></a></h3>
    </div>
    <div id="footer-copyright">
        <small id="copyright">&copy; <?php echo Date("Y") ?> <?php echo $copyrightholder ?></small>
    </div>
    <div id="footer-logo">
        <img alt="Powered by OHMS logo" src="imgs/ohms_logo.png" border="0"/>
    </div>
    <br clear="both" />
</div>
