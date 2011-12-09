<div id="column1">
    <form action="/search" method="post">
            <input type="text" id="search" name="words"  value="Поиск"/>
    </form>

    %structure.objList(news_feed, newsandarticles)%
    %structure.menu(second, 1, 339)%


    %voting.objList(all, default, 1)%
    
    %subscription.form()%


    
</div>
    