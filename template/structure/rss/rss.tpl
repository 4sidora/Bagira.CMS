<?php

$TEMPLATE['frame_list'] = <<<END
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>%channel.title%</title>
        <link>%channel.url%</link>
        <description>%channel.notice%</description>
        <lastBuildDate>%channel.date%</lastBuildDate>
        <language>ru</language>

        %list%

    </channel>
</rss>
END;

$TEMPLATE['list'] = <<<END

     <item>
         <title>%obj.name%</title> 
         <link>%obj.url%</link>
         <description>%obj.notice%</description>
         <pubDate>%obj.date%</pubDate>
         <guid isPermaLink="false">%obj.id%</guid>
     </item>

END;
/*
$TEMPLATE['media'] = <<<END\
     <enclosure url="%obj.media_url%" type="audio/mpeg"/>
END;
*/
?>