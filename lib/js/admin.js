$(document).ready(function($){

    LoadPage($('input[name="startPage"]').val());


    $("#menu>a").button({
        icons: {primary:'ui-icon-play'}
    });
    $("#menu>a:first").button({
        icons: {primary:'ui-icon-home'}
    });
    $("#menu>a:last").button({
        icons: {primary:'ui-icon-closethick'}
    });

    $("a[href*='.php']").live('click', function(e){
        e.preventDefault();
        LoadPage($(this).attr("href"));
    });

    $("input[name='assinatura']").live("blur",function(){
        $("input[name='dataRenovacao'].novoContrato").val($("input[name='assinatura']").val());
    });


    $("button[type='submit']").live("click",function(){
        var ele = $("form");
        var flag = 0;

        switch($(this).attr("id")){
            case "btnExcluir":
                ele = $("#fLista");
                flag = 1;
                break;
            case "btnformJanela":
                flag = 2;
                break;
            case "btnformJanelaGrid":
                flag = 3;
                break;
            case "btnExcluirItem":
                ele.find("input[name='acao']").attr("value", "ext");
                break
            case "btnExcluirDespesa":
                ele.find("input[name='acao']").attr("value", "remove");
                break;
        }
        ele.validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    datatype:'html',
                    clearForm: true,
                    success: function(data){
                        $("body").append("<div id='dialog-modal' />").find("#dialog-modal").html(data);
                        $("#dialog-modal").dialog({
                            modal:true,
                            show:"fade", 
                            buttons: {
                                    Ok: function() {
                                            $( this ).dialog( "close" );
                                    }
                            },
                            close:function(event,ui){
                                $("#dialog-modal").remove();
                            },
                            open: function() {
                                $('.ui-dialog-buttonpane')
                                    .find('button:contains("Ok")')
                                    .button({
                                         icons: {
                                            primary: "ui-icon-circle-check"
                                        }
                                    })
                            }
                        });
                        switch (flag){
                            case 1:
                                LoadPage($("#lista").attr("href"));
                                break;
                            case 2:
                                break;
                            case 3:
                                $id = $("#dialog-add").find("#ref").val();
                                $c = $("#dialog-add").find("input[name='id']").val();

                                $("#"+$id+"Wraper").load('Frontend/contrato/sub-contrato/inc/'+$id+'Lista.php?subcontrato='+$c,function(data){
                                    $(this).html(data);
                                    botoniza();
                                });
                                $("#dialog-add").dialog("close");
                                break;
                            default:
                                $(form).find(".buttonVoltar").click();
                                break;
                        }
                    }
                });
            }
        });
    });
});

// Recebe uma URL e remove o nome de arquivo e a query String
function GetURLPath(url) {
    var path = url.split('/');

    var urlPath = '';
    for (var index = 0; index < (path.length-1); index++)
    {
        urlPath = urlPath + path[index] + '/';
    }

    return urlPath;
}

function botoniza(){
    $("#checkall").live('click',function(){
        $("#fLista input[type='checkbox']").check();
    });
    
    $("#uncheckall").live('click',function(){
        $("#fLista input[type='checkbox']").check('off');
    });

    $("#fLista").find(".button:first").button({
        icons: {primary:'ui-icon-bullet'}
    }).next(".button").button({
        icons: {primary:'ui-icon-radio-off'}
    }).next(".button").button({
        icons: {primary:'ui-icon-circle-plus'}
    }).next(".button").button({
        icons: {primary:'ui-icon-circle-minus'}
    }).next(".button").button({
        icons: {primary:'ui-icon-circle-check'}
    }).next(".button").button({
        icons: {primary:'ui-icon-circle-close'}
    });

    $("#fLista").find(".buttonVoltar").button({
        icons: {primary:'ui-icon-arrowreturnthick-1-w'}
    });

    $(".buttonVoltar").button({
        icons: {primary:'ui-icon-arrowreturnthick-1-w'}
    });

    $(".sorTable")
    .addClass("ui-widget ui-widget-content ui-corner-all")
    .enhanceTable({singleSelect:false});

    $(".sorTable")
        .tablesorter()
        .tablesorterFilter({
                          filterContainer: $("#filter-box"),
                          filterClearContainer: $("#filter-clear-button"),
                          filterColumns: [1,2,3,4,5,6,7,8,9],
                          filterCaseSensitive: false
                        })
        .tablesorterPager({container: $(".pagerListar"),size:10});

    $("#filter-clear-button").button({
        icons: {primary: "ui-icon-closethick"},text:false
    });

    $(".pagerListar").each(function(){
        $(this).find(".first").button({
            icons: {primary: "ui-icon-seek-first"},text:false
        });
        $(this).find(".prev").button({
            icons: {primary: "ui-icon-seek-prev"},text:false
        });
        $(this).find(".next").button({
            icons: {primary: "ui-icon-seek-next"},text:false
        });
        $(this).find(".last").button({
            icons: {primary: "ui-icon-seek-end"},text:false
        });
    });
   

    $('input[type=text],input[type=password]').each(function(){
        $(this).addClass('flickTextBox');
    });

    //$(".multiselect").multiselect({sortable:true});

    $("form").find(".buttonVoltar").button({
        icons: {primary:'ui-icon-arrowreturnthick-1-w'}
    });
    $("#btnform,#btnformJanela,#btnformJanelaGrid").button({
        icons: {primary:'ui-icon-circle-check'}
    });
    $("#btnExcluirItem").button({
        icons: {primary:'ui-icon-closethick'}
    });
    $("#btnExcluirDespesa").button({
        icons: {primary:'ui-icon-closethick'}
    });
    $(".renew").button({
        icons: {primary:'ui-icon-arrowrefresh-1-n'}
    });

    if($(".addSub").attr("href") != "#"){
        $disable = false;
    } else {
        $disable = true;
    }
    if($(".addItemPedido").attr("href") != "#"){
        $disable = false;
    } else {
        $disable = true;    	
    }
    if($(".addDespesa").attr("href") != "#"){
        $disable = false;
    } else {
        $disable = true;    	
    }

    $(".addSub").button({
        icons: {primary: "ui-icon-plus"},
        disabled:$disable
    });
    $(".addItemPedido").button({
        icons: {primary: "ui-icon-plus"},
        disabled:$disable    	
    });
    $(".addDespesa").button({
        icons: {primary: "ui-icon-plus"},
        disabled:$disable    	
    });

    $('.datepick').datepicker({
        dateFormat: 'd-m-yy'
    });
}
function LoadPage(url){
    $.get(url, function(data){
        $("#lista").hide("fold","fast", function(){
            $(this).html(data).show("fold","fast");
            $(this).attr("href", url);

            botoniza();

            $(".addMore").each(function(){
                $text = true;
                                
                if($(this).text() == "noText"){
                    $text = false;
                }
                $(this).button({
                icons: {primary:'ui-icon-circle-plus'},
                text:$text
                }).click(function(){
                        $click = $(this);
                        $href = $(this).attr("href");
                        $title = $(this).attr("title");
                        $ref = $(this).prev().attr("name");


                        if ($("#dialog-add").length){
                            $("#dialog-add").remove();
                            $("#dialog-add").dialog("destroy");
                        } 
                        $("body").append("<div id='dialog-add'></div>");

                        $("#dialog-add").load($href,function(){
                            $(this).append("<input type='hidden' id='ref' value='"+$ref+"'/>");
                            $(this).dialog({
                                close:function(){
                                    $click.trigger("blur");
                                    $(this).remove();
                                },
                                open:function(event, ui){
                                    botoniza();
                                },
                                modal:true,
                                resizable:false,
                                title: $title
                            });
                        });
                    return false;
                });
            });

            $(".removeMore").button({
                icons: {primary:'ui-icon-circle-minus'},
                text:false
            }).click(function(){
                $reg = $(this).attr("ref");
                $regItem = $("select[name='"+$reg+"']").val();
                $regText = $("select[name='"+$reg+"'] :selected").text();
                
                if(confirm("Deseja excluir o item "+$regText+"?")){
                    alert('not implemented');
                }

                return false;
            });

            $(".extGrid").live("click",function(){

                $reg = $(this).attr("ref");
                $this = $(this);

                if(confirm("Confirma exclusão?")){
                    $.post($(this).attr("href"), function(data){
                        alert(data);

                        $c = $this.attr("rel");
                        $id = $this.attr("title");

                        $("#"+$id+"Wraper").load('Frontend/contrato/sub-contrato/inc/'+$id+'Lista.php?subcontrato='+$c,function(data){ 
                            botoniza();
                        });
                    });
                }
                return false;
            });
        });
    });
}


