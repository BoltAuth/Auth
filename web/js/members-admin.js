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

var MembersAdmin = Object.extend(
    Object, {

        selector: ".members-bolt-container",
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
            var selected_members = [];

            console.debug("Adding a user");

            $.post(baseurl + '/userAdd', {member: selected_members}, function(selected_members) {})
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
            var selected_members = [];

            $.each(
                $("input[name='form[members][]']:checked"), function() {
                    selected_members.push($(this).val());
                }
            );

            console.debug("Deleting user(s): " + selected_members);

            $.post(baseurl + '/userDel', {members: selected_members}, function(selected_members) {})
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

        doUserEnable: function(e) {
            var controller = this;
            var selected_members = [];

            $.each(
                $("input[name='form[members][]']:checked"), function() {
                    selected_members.push($(this).val());
                }
            );

            if (selected_members == []) {
                swal({title: "Nothing Selected!", text: "You need to chose a member.", type: "warning"});
                return;
            }

            console.debug("Enabling user(s): " + selected_members);

            $.post(baseurl + '/userEnable', {members: selected_members}, function(selected_members) {})
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
            var selected_members = [];

            $.each(
                $("input[name='form[members][]']:checked"), function() {
                    selected_members.push($(this).val());
                }
            );

            if (selected_members == '') {
                swal({title: "Nothing Selected!", text: "You need to chose a member.", type: "warning"});
                return;
            }

            console.debug("Disabling user(s): " + selected_members);

            $.post(baseurl + '/userDisable', {members: selected_members}, function(selected_members) {})
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
            var selected_members = [];
            var selected_role = $(".members-select-roles :selected").val();

            $.each(
                $("input[name='form[members][]']:checked"), function() {
                    selected_members.push($(this).val());
                }
            );

            if (selected_members == '') {
                swal({title: "Nothing Selected!", text: "You need to chose a member.", type: "warning"});
                return;
            }

            if (selected_role == '') {
                swal({title: "Nothing Selected!", text: "You need to chose a role.", type: "warning"});
                return;
            }

            console.debug("Adding role '" + selected_role + "' to user(s): " + selected_members);

            $.post(
                    baseurl + '/roleAdd',
                {members: selected_members, role: selected_role},
                function(selected_members, selected_role) {}
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
            var selected_members = [];
            var selected_role = $(".members-select-roles :selected").val();

            $.each(
                $("input[name='form[members][]']:checked"), function() {
                    selected_members.push($(this).val());
                }
            );

            if (selected_members == '') {
                swal({title: "Nothing Selected!", text: "You need to chose a member.", type: "warning"});
                return;
            }

            if (selected_role == '') {
                swal({title: "Nothing Selected!", text: "You need to chose a role.", type: "warning"});
                return;
            }

            console.debug("Removing role '" + selected_role + "' from user(s): " + selected_members);

            $.post(
                    baseurl + '/roleDel',
                {members: selected_members, role: selected_role},
                function(selected_members, selected_role) {}
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
                    case "members-user-add"     :
                        controller.doUserAdd(e.originalEvent);
                        break;
                    case "members-user-del"     :
                        controller.doUserDel(e.originalEvent);
                        break;
                    case "members-user-enable"  :
                        controller.doUserEnable(e.originalEvent);
                        break;
                    case "members-user-disable" :
                        controller.doUserDisable(e.originalEvent);
                        break;
                    case "members-role-add"     :
                        controller.doRoleAdd(e.originalEvent);
                        break;
                    case "members-role-del"     :
                        controller.doRoleDel(e.originalEvent);
                        break;
                }
            }
        }
    }
);
