// Portions, or more, of this taken from work by Steven de Salas
// http://desalasworks.com/article/object-oriented-javascript-inheritance/

// Create a static 'extends' method on the Object class
// This allows us to extend existing classes
// for classical object-oriented inheritance
Object.extend = function(superClass, definition) {
    var subClass = function() {};
    // Our constructor becomes the 'subclass'
    if (definition.constructor !== Object)
        subClass = definition.constructor;
    subClass.prototype = new superClass();
    for (var prop in definition) {
        if (prop != 'constructor')
            subClass.prototype[prop] = definition[prop];
    }
    return subClass;
};

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

var MembersAdmin = Object.extend(Object, {

    selector: ".members-bolt-container",
    messages:  {},
    paths:  {},

    constructor: function(){
        jQuery(this.selector).on("change", this, this.events.change);
        jQuery(this.selector).on("click", this, this.events.click);
    },
    
    find: function(selector) {
        return jQuery(this.selector).find(selector);
    },
    
    setMessage: function(key, value) {
        this.messages[key]=value;
    },

    
    setPath: function(key, value) {
        this.paths[key]=value;
    },
    
    doUserAdd: function(e) {
        var controller = this;
        var data = new Array();

        console.debug("Adding a user");

        $.post(baseurl + '/ajax?task=userAdd', {members: data}, function(data){})
            .done(function() {
            	swal({ title: "", text: "Feature coming soon!", type: "info" });
//                location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
    
    doUserDel: function(e) {
        var controller = this;
        var data = new Array();
        
        $.each($("input[name='form[members][]']:checked"), function () {
            data.push($(this).val());
        });

        console.debug("Deleting user(s): " + data);

        $.post(baseurl + '/ajax?task=userDel', {members: data}, function(data){})
            .done(function() {
            	swal({ title: "", text: "Feature coming soon!", type: "info" });
//                location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
    
    doUserEnable: function(e) {
        var controller = this;
        var data = new Array();
        
        $.each($("input[name='form[members][]']:checked"), function () {
            data.push($(this).val());
        });
        
        if (data == '') {
            swal({ title: "Nothing Selected!", text: "You need to chose a member.", type: "warning" });
            return;
        }
        
        console.debug("Enabling user(s): " + data);

        $.post(baseurl + '/ajax?task=userEnable', {members: data}, function(data){})
            .done(function() {
            	location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
    
    doUserDisable: function(e) {
        var controller = this;
        var data = new Array();
        
        $.each($("input[name='form[members][]']:checked"), function () {
            data.push($(this).val());
        });
        
        if (data == '') {
            swal({ title: "Nothing Selected!", text: "You need to chose a member.", type: "warning" });
            return;
        }
        
        console.debug("Disabling user(s): " + data);

        $.post(baseurl + '/ajax?task=userDisable', {members: data}, function(data){})
            .done(function() {
            	location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
    
    doRoleAdd: function(e) {
        var controller = this;
        var data = new Array();
        
        $.each($("input[name='form[members][]']:checked"), function () {
            data.push($(this).val());
        });
        
        if (data == '') {
            swal({ title: "Nothing Selected!", text: "You need to chose a member.", type: "warning" });
            return;
        }
        
        console.debug("Adding role to user(s): " + data);

        $.post(baseurl + '/ajax?task=roleAdd', {members: data}, function(data){})
            .done(function() {
            	swal({ title: "", text: "Feature coming soon!", type: "info" });
//              location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
    
    doRoleDel: function(e) {
        var controller = this;
        var data = new Array();
        
        $.each($("input[name='form[members][]']:checked"), function () {
            data.push($(this).val());
        });
        
        if (data == '') {
            swal({ title: "Nothing Selected!", text: "You need to chose a member.", type: "warning" });
            return;
        }
        
        console.debug("Removing role from user(s): " + data);

        $.post(baseurl + '/ajax?task=roleDel', {members: data}, function(data){})
            .done(function() {
            	swal({ title: "", text: "Feature coming soon!", type: "info" });
//              location.reload(true);
                })
            .fail(function() {
                swal({ title: "Error!", text: "The server returned an error.", type: "error" });
                })
    },
        
    events: {
        change: function(e, t){
            var controller = e.data;
        },
        
        click: function(e, t){
            var controller = e.data;
            switch(jQuery(e.target).data('action')) {
                case "members-user-add"     : controller.doUserAdd(e.originalEvent); break;
                case "members-user-del"     : controller.doUserDel(e.originalEvent); break;
                case "members-user-enable"  : controller.doUserEnable(e.originalEvent); break;
                case "members-user-disable" : controller.doUserDisable(e.originalEvent); break;
                case "members-role-add"     : controller.doRoleAdd(e.originalEvent); break;
                case "members-role-del"     : controller.doRoleDel(e.originalEvent); break;
            }
        }

    }

});