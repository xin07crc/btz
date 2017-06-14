//get the IP addresses associated with an account
function getIPs(callback){
    var ip_dups = {};

    //compatibility for firefox and chrome
    var RTCPeerConnection = window.RTCPeerConnection
        || window.mozRTCPeerConnection
        || window.webkitRTCPeerConnection;
    var useWebKit = !!window.webkitRTCPeerConnection;

    //bypass naive webrtc blocking using an iframe
    if(!RTCPeerConnection){
        return false;
        //NOTE: you need to have an iframe in the page right above the script tag
        //
        //<iframe id="iframe" sandbox="allow-same-origin" style="display: none"></iframe>
        //<script>...getIPs called in here...
        //
        var win = iframe.contentWindow;
        RTCPeerConnection = win.RTCPeerConnection
            || win.mozRTCPeerConnection
            || win.webkitRTCPeerConnection;
        useWebKit = !!win.webkitRTCPeerConnection;
    }

    //minimal requirements for data connection
    var mediaConstraints = {
        optional: [{RtpDataChannels: true}]
    };

    var servers = {iceServers: [{urls: "stun:stun.services.mozilla.com"}]};

    //construct a new RTCPeerConnection
    var pc = new RTCPeerConnection(servers, mediaConstraints);

    function handleCandidate(candidate){
        //match just the IP address
        var ip_regex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/
        var ip_addr = ip_regex.exec(candidate)[1];

        //remove duplicates
        if(ip_dups[ip_addr] === undefined)
            callback(ip_addr);

        ip_dups[ip_addr] = true;
    }

    //listen for candidate events
    pc.onicecandidate = function(ice){

        //skip non-candidate events
        if(ice.candidate)
            handleCandidate(ice.candidate.candidate);
    };

    //create a bogus data channel
    pc.createDataChannel("");

    //create an offer sdp
    pc.createOffer(function(result){

        //trigger the stun server request
        pc.setLocalDescription(result, function(){}, function(){});

    }, function(){});

    //wait for a while to let everything done
    setTimeout(function(){
        //read candidate info from local description
        var lines = pc.localDescription.sdp.split('\n');

        lines.forEach(function(line){
            if(line.indexOf('a=candidate:') === 0)
                handleCandidate(line);
        });
    }, 1000);
}

/**
 * Comenta las tareas
 */
function comentar(id_article, name) {
    /**
     * Abre mensaje para comentar la tarea
     */
    swal({
        title: '<p translate>' + name + '<br>Write a comment on the task</p>',
        text: '<label translate>Type</label><br><select class="form-control" id="tipo"><option selected value="" translate>Your option</option><option value="Usability"  translate>Usability</option><option value="Design"  translate>Design </option><option value="Functioning"  translate>Functioning </option><option value="Utility"  translate>Utility </option><option value="Error detected"  translate>Error detected </option><option value="Others"  translate>Others </option></select><br><br><label  translate>Message</label><br><textarea  class="form-control" id="mensaje"></textarea>',
        showCancelButton: true,
        confirmButtonText: 'Send',
        showLoaderOnConfirm: true,
        preConfirm: function (email) {
            var tipo = $("#tipo").val();
            var mensaje = $("#mensaje").val();
            var titulo = $("#titulo").val();
            console.log(mensaje.length);

            return new Promise(function (resolve, reject) {
                if (tipo != "" && mensaje.length > 3) {
                    $.post( "test/test_ionic/www/php/comentar.php", {
                        id_article:  id_article,
                        tipo : tipo,
                        mensaje : mensaje
                    }, function( data ) {
                        data = JSON.parse(data);
                        console.log(data);
                        if (data["error"] == true) {
                            switch (data["cod"]) {
                                case 1 :
                                    reject('Both fields are required');
                                    break;
                                default:
                                    reject('Error uploading comment');
                                    break;
                            }
                        }
                        else {
                            resolve();
                        }
                    });
                }
                else {
                    reject('Questionnaire not fully complete, please select any option and the comment must have at least 3 characters');
                }
            })
        },
        allowOutsideClick: false
    }).then(function (email) {
        swal({
            type: 'success',
            title: 'Message sent',
        })
    })
}