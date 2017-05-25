// Portions, or more, of this taken from work by Steven de Salas
// http://desalasworks.com/article/object-oriented-javascript-inheritance/

// Create a static 'extends' method on the Object class
// This allows us to extend existing classes
// for classical object-oriented inheritance
Object.extend = function(superClass, definition) {
    var subClass = function() {};
    // Our constructor becomes the 'subclass'
    if (definition.constructor !== Object) {
        subClass = definition.constructor;
    }
    subClass.prototype = new superClass();
    for (var prop in definition) {
        if (prop != 'constructor') {
            subClass.prototype[prop] = definition[prop];
        }
    }
    return subClass;
};

var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

var AuthAdmin = Object.extend(
    Object, {

        selector: ".auth-bolt-container",
        messages: {},
        paths: {},

        constructor: function() {
            jQuery(this.selector).on("change", this, this.events.change);
            jQuery(this.selector).on("click", this, this.events.click);
        },

        find: function(selector) {
            return jQuery(this.selector).find(selector);
        },

        setMessage: function(key, value) {
            this.messages[key] = value;
        },


        setPath: function(key, value) {
            this.paths[key] = value;
        },

        doUserAdd: function(e) {
            var controller = this;
            var selected_auth = [];

            console.debug("Adding a user");

            $.post(baseurl + '/userAdd', {auth: selected_auth}, function(selected_auth) {})
                .done(
                    function() {
                        swal({title: "", text: "Feature coming soon!", type: "info"});
                        //                location.reload(true);
                    }
                )
                .fail(
                    function() {
                        swal({title: "Error!", text: "The server returned an error.", type: "error"});
                    }
                )
        },

        doUserDel: function(e) {
            var controller = this;
            var selected_auth = [];

            $.each(
                $("input[name='form[auth][]']:checked"), function() {
                    selected_auth.push($(this).val());
                }
            );

            if (selected_auth.length === 0) {
                swal({title: "Nothing Selected!", text: "You need to choose at least one auth.", type: "warning"});
                return;
            }

            console.debug("Deleting user(s): " + selected_auth);

            swal({
                title: "Confim deletion",
                text: "Are you sure you want to delete these accounts?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes!",
                closeOnConfirm: false },
                function(){
                    $.post(baseurl + '/userDelete', {auth: selected_auth}, function(selected_auth) {})
                        .done(
                            function() {
                                location.reload(true);
                            }
                        )
                        .fail(
                            function() {
                                swal({title: "Error!", text: "The server returned an error.", type: "error"});
                            }
                        )
                }
            );
        },

        doUserEnable: function(e) {
            var controller = this;
            var selected_auth = [];

            $.each(
                $("input[name='form[auth][]']:checked"), function() {
                    selected_auth.push($(this).val());
                }
            );

            if (selected_auth.length === 0) {
                swal({title: "Nothing Selected!", text: "You need to choose a auth.", type: "warning"});
                return;
            }

            console.debug("Enabling user(s): " + selected_auth);

            $.post(baseurl + '/userEnable', {auth: selected_auth}, function(selected_auth) {})
                .done(
                    function() {
                        location.reload(true);
                    }
                )
                .fail(
                    function() {
                        swal({title: "Error!", text: "The server returned an error.", type: "error"});
                    }
                )
        },

        doUserDisable: function(e) {
            var controller = this;
            var selected_auth = [];

            $.each(
                $("input[name='form[auth][]']:checked"), function() {
                    selected_auth.push($(this).val());
                }
            );

            if (selected_auth == '') {
                swal({title: "Nothing Selected!", text: "You need to choose a auth.", type: "warning"});
                return;
            }

            console.debug("Disabling user(s): " + selected_auth);

            $.post(baseurl + '/userDisable', {auth: selected_auth}, function(selected_auth) {})
                .done(
                    function() {
                        location.reload(true);
                    }
                )
                .fail(
                    function() {
                        swal({title: "Error!", text: "The server returned an error.", type: "error"});
                    }
                )
        },

        doRoleAdd: function(e) {
            var controller = this;
            var selected_auth = [];
            var selected_role = $(".auth-select-roles :selected").val();

            $.each(
                $("input[name='form[auth][]']:checked"), function() {
                    selected_auth.push($(this).val());
                }
            );

            if (selected_auth == '') {
                swal({title: "Nothing Selected!", text: "You need to choose a auth.", type: "warning"});
                return;
            }

            if (selected_role == '') {
                swal({title: "Nothing Selected!", text: "You need to choose a role.", type: "warning"});
                return;
            }

            console.debug("Adding role '" + selected_role + "' to user(s): " + selected_auth);

            $.post(
                    baseurl + '/roleAdd',
                {auth: selected_auth, role: selected_role},
                function(selected_auth, selected_role) {}
                )
                .done(
                    function() {
                        swal(
                            {title: "", text: "Added role: " + selected_role, type: "info"},
                            function(isConfirm) {
                                if (isConfirm) {
                                    location.reload(true);
                                }
                            }
                        );
                    }
                )
                .fail(
                    function() {
                        swal({title: "Error!", text: "The server returned an error.", type: "error"});
                    }
                )
        },

        doRoleDel: function(e) {
            var controller = this;
            var selected_auth = [];
            var selected_role = $(".auth-select-roles :selected").val();

            $.each(
                $("input[name='form[auth][]']:checked"), function() {
                    selected_auth.push($(this).val());
                }
            );

            if (selected_auth == '') {
                swal({title: "Nothing Selected!", text: "You need to choose a auth.", type: "warning"});
                return;
            }

            if (selected_role == '') {
                swal({title: "Nothing Selected!", text: "You need to choose a role.", type: "warning"});
                return;
            }

            console.debug("Removing role '" + selected_role + "' from user(s): " + selected_auth);

            $.post(
                    baseurl + '/roleDel',
                {auth: selected_auth, role: selected_role},
                function(selected_auth, selected_role) {}
                )
                .done(
                    function() {
                        swal(
                            {title: "", text: "Removed role: " + selected_role, type: "info"},
                            function(isConfirm) {
                                if (isConfirm) {
                                    location.reload(true);
                                }
                            }
                        );
                    }
                )
                .fail(
                    function() {
                        swal({title: "Error!", text: "The server returned an error.", type: "error"});
                    }
                )
        },

        events: {
            change: function(e, t) {
                var controller = e.data;
            },

            click: function(e, t) {
                var controller = e.data;
                switch (jQuery(e.target).data('action')) {
                    case "auth-user-add"     :
                        controller.doUserAdd(e.originalEvent);
                        break;
                    case "auth-user-del"     :
                        controller.doUserDel(e.originalEvent);
                        break;
                    case "auth-user-enable"  :
                        controller.doUserEnable(e.originalEvent);
                        break;
                    case "auth-user-disable" :
                        controller.doUserDisable(e.originalEvent);
                        break;
                    case "auth-role-add"     :
                        controller.doRoleAdd(e.originalEvent);
                        break;
                    case "auth-role-del"     :
                        controller.doRoleDel(e.originalEvent);
                        break;
                }
            }
        }
    }
);
