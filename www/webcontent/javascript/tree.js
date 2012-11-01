function collapse(topic_id,collapseCallback) {
    //collapse
    Element.hide('min_topic_'+topic_id);
    Element.show('plus_topic_'+topic_id);
    Element.hide('topic_children_'+topic_id);
    //call callback if not empty
    if (collapseCallback != "") {
        new Ajax.Updater('',collapseCallback,{evalScripts:true});
    }
}
function expand(topic_id,expandCallback) {
    //collapse
    Element.show('min_topic_'+topic_id);
    Element.hide('plus_topic_'+topic_id);
    Element.show('topic_children_'+topic_id);
    //call callback if not empty
    if (expandCallback != "") {
        new Ajax.Updater('',expandCallback,{evalScripts:true});
    }
}
/* subscriptionCallback is an url that must be suffixed with '/(un)subscribe/topic_id/user_id' */
function toggleSubscription(user_id,topic_id,subscriptionCallback) {
    var el = $('subscription_'+topic_id);
    //toggle class and call async (un)subscription controller
    if(Element.hasClassName(el,'subscribedtopic')) {
        //was subscribed 
        new Ajax.Updater('',subscriptionCallback+'/unsubscribe/'+topic_id+'/'+user_id,{evalScripts:true});
        Element.removeClassName(el,'subscribedtopic');
        Element.addClassName(el,'unsubscribedtopic');
        MarkUnSub(topic_id);
    } else {
        //was unsubscribed 
        new Ajax.Updater('',subscriptionCallback+'/subscribe/'+topic_id+'/'+user_id,{evalScripts:true});
        Element.removeClassName(el,'unsubscribedtopic');
        Element.addClassName(el,'subscribedtopic');
        MarkSub(topic_id);
    }
}


function MarkSub(id)
{
    var subs = $$('#topic_children_'+id+' .unsubscribedtopic');
    for(var c = 0; c < subs.length; c++)
    {
        Element.removeClassName($(subs[c]),'unsubscribedtopic');
        Element.addClassName($(subs[c]),'subscribedtopic');
    }
}

function MarkUnSub(id)
{
    var subs = $$('#topic_children_'+id+' .subscribedtopic');
    for(var c = 0; c < subs.length; c++)
    {
        Element.removeClassName($(subs[c]),'subscribedtopic');
        Element.addClassName($(subs[c]),'unsubscribedtopic');
    }
}

/* subscriptionCallback is an url that must be suffixed with '/(un)subscribe/topic_id/pub_id' */
function togglePublicationSubscription(pub_id,topic_id,subscriptionCallback) {
    var el = $('subscription_'+topic_id);
    //toggle class and call async (un)subscription controller
    if(Element.hasClassName(el,'subscribedtopic')) {
        //was subscribed 
        new Ajax.Updater('',subscriptionCallback+'/unsubscribe/'+topic_id+'/'+pub_id,{evalScripts:true});
        Element.removeClassName(el,'subscribedtopic');
        Element.addClassName(el,'unsubscribedtopic');
        MarkUnSub(topic_id);
    } else {
        //was unsubscribed 
        new Ajax.Updater('',subscriptionCallback+'/subscribe/'+topic_id+'/'+pub_id,{evalScripts:true});
        Element.removeClassName(el,'unsubscribedtopic');
        Element.addClassName(el,'subscribedtopic');
        MarkSub(topic_id);
    }
}