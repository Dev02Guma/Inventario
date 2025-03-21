<script type="text/javascript">
    var Selectors = {
        TABLE_SETTING: '#modal_new_product',
        TABLE_UPLOARD: '#modal_upload'
    };

    dta_table_excel = [];
    var isError = false
    var TableExcel;
   
    $(document).ready(function () {
        

       
        $('#id_txt_buscar').on('keyup', function() {   
            var vTableArticulos = $('#tbl_productos').DataTable();     
            vTableArticulos.search(this.value).draw();
        });
        $('#id_txt_excel').on('keyup', function() {    
            if(isValue(TableExcel,0,true)){
                TableExcel.search(this.value).draw();
            }
        });
        

        $("#btn_upload").click(function(){

            

            var addMultiRow = document.querySelector(Selectors.TABLE_UPLOARD);
            var modal = new window.bootstrap.Modal(addMultiRow);
            modal.show();


        });

        $('#frm-upload').on("change", function(e){ 
            handleFileSelect(e)
        });

        initTable('#tbl_productos');
    });

  
    function OpenModal(Articulo){
        var HeaderArticulo = Articulo.DESCRIPCION 
        var FooterArticulo = Articulo.ARTICULO + " | " + Articulo.UND


        $("#articulos_header").text(HeaderArticulo) 
        $("#articulos_footer").text(FooterArticulo)

        var _CANTIDAD = numeral(Articulo.CANTIDAD).format('0,0.00')

        var _JUMBOS = numeral(Articulo.JUMBOS).format('0.00')
        var _FISICO = numeral(Articulo.CANTIDAD).format('0.00')

        $("#id_existencia_actual").text(_CANTIDAD  + " " + Articulo.UND) 
        $("#id_created_at").text(Articulo.CREATED_AT) 
        
        $("#art_code").val(Articulo.ID)
        
        $("#art_cant_ingreso").val(_FISICO)
        $("#id_jumbos").val(_JUMBOS)

        var TABLE_SETTING = document.querySelector(Selectors.TABLE_SETTING);
        var modal = new window.bootstrap.Modal(TABLE_SETTING);
        modal.show();
    }

    function initTable(id){
        $(id).DataTable({
        "destroy": true,
        "info": false,
        "bPaginate": true,
        "order": [
            [0, "asc"]
        ],
        "lengthMenu": [
            [7, -1],
            [7, "Todo"]
        ],
        "language": {
            "zeroRecords": "NO HAY COINCIDENCIAS",
            "paginate": {
                "first": "Primera",
                "last": "Última ",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "lengthMenu": "MOSTRAR _MENU_",
            "emptyTable": "-",
            "search": "BUSCAR"
        },
        });

        $(id+"_length").hide();
        $(id+"_filter").hide();
    }
   
    function table_render(Table,datos,Header,Filter){

        TableExcel = $(Table).DataTable({
            "data": datos,
            "destroy": true,
            "info": false,
            "bPaginate": true,
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, -1],
                [10, "Todo"]
            ],
            "language": {
                "zeroRecords": "NO HAY COINCIDENCIAS",
                "paginate": {
                    "first": "Primera",
                    "last": "Última ",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "lengthMenu": "MOSTRAR _MENU_",
                "emptyTable": "-",
                "search": "BUSCAR"
            },
            'columns': Header,
           
            rowCallback: function( row, data, index ) {
                if ( data.Index == 'N/D' ) {
                    $(row).addClass('table-danger');
                } 
            }
        });
        if(!Filter){
            $(Table+"_length").hide();
            $(Table+"_filter").hide();
        }

    }
    var ExcelToJSON = function() {

        this.parseExcel = function(file) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var data = e.target.result;
            var workbook = XLSX.read(data, {type: 'binary'});
           
            workbook.SheetNames.forEach(function(sheetName) {

               

                if(sheetName=='INVENTARIO'){
                    dta_table_excel = [];
                    isError=false;

                    var isVal = 0;
                    var NotVal = 0;
                    var ttSkus = 0;

                    var worksheet = workbook.Sheets[sheetName];
                    var range = XLSX.utils.decode_range('A1:E200');
                    var rows = XLSX.utils.sheet_to_json(worksheet, {range: range});

                    rows.forEach(function(row) {

                        var _Codigo   = isValue(row.ARTICULO,'N/D',true)
                        var index     = _Codigo;
                        var _Total    = numeral(isValue(row.FISICO,'0',true)).format('00.00')
                        var _Unida    = isValue(row.UNIDAD,'N/D',true)
                        var _Jumbo    = numeral(isValue(row.JUMBOS,'0',true)).format('00.00')
                        var _Descr    = isValue(row["DESCRIPCION"],'Columna <strong>"Descripcion de Producto"</strong> no Encontrada',true)

                        if(_Codigo == 'N/D'){
                            isError=true
                            NotVal++
                        }

                        if (/^[0-9N]/.test(_Codigo.charAt(0))){
                            dta_table_excel.push({ 
                                Articulo: _Codigo,
                                Descr   : _Descr,
                                Unida   : _Unida,
                                Total   : _Total,
                                Jumbo   : _Jumbo
                            })
                            
                        }
                    });

                    ttSkus = dta_table_excel.length;
                    isVal   = numeral(isValue((ttSkus - NotVal),'0',true)).format('0,0')
                    NotVal  = numeral(isValue(NotVal,'0',true)).format('0,0')

                    AVGValido = (isVal / ttSkus) * 100;
                    AVGValido  = numeral(isValue(AVGValido,'0',true)).format('0.00')+ "%"

                    AVGNotValido = (NotVal / ttSkus) * 100;
                    AVGNotValido  = numeral(isValue(AVGNotValido,'0',true)).format('0.00') + "%"

                    
                    $("#ttSKUs").text(dta_table_excel.length) 
                    $("#ttSKUsValido").text(isVal)
                    $("#avgValido").text(AVGValido)
                    $("#avgNotValido").text(AVGNotValido)
                    $("#ttSKUsNotValido").text(NotVal)

                    if(isError){
                        Swal.fire("Codigo de Articulo No encontrado", "Existen articulos sin Definicion de Codigo ", "error");
                    }


                    dta_table_header = [
                        {"title": "Articulo","data": "Articulo"},
                        {"title": "Descripcion","data": "Descr"}, 
                        {"title": "Unidad","data": "Unida"},                                     
                        {"title": "Fisica","data": "Total"},
                        {"title": "Jumbo","data": "Jumbo"},
                    ]
                    table_render('#tbl_excel',dta_table_excel,dta_table_header,false)
                }
            })
        };

        reader.onerror = function(ex) {

        };

        reader.readAsBinaryString(file);

        };
    };

    $("#id_send_data_excel").click(function(){ 
     
        
        if(!isError){
        Swal.fire({
            title: '¿Estas Seguro de cargar  ?',
            text: "¡Se cargara la informacion previamente visualizada!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si!',
            target: document.getElementById('mdlMatPrima'),
            showLoaderOnConfirm: true,
            preConfirm: () => {
                $.ajax({
                    url: "GuardarInventario",
                    data: {
                        datos   : dta_table_excel,
                        _token  : "{{ csrf_token() }}" 
                    },
                    type: 'post',
                    async: true,
                    success: function(response) {
                        if(response){
                            Swal.fire({
                                title: 'Articulos Ingresados Correctamente ' ,
                                icon: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'OK'
                                }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                    }
                                })
                            }
                        },
                    error: function(response) {
                        //Swal.fire("Oops", "No se ha podido guardar!", "error");
                    }
                    }).done(function(data) {
                        //CargarDatos(nMes,annio);
                    });
                },
            allowOutsideClick: () => !Swal.isLoading()
        });

            
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "Existen articulos sin Definicion de Codigo ",
                
            })
        }
    })
 
    function handleFileSelect(evt) {    
        var files = evt.target.files;
        var xl2json = new ExcelToJSON();
        xl2json.parseExcel(files[0]);
    }
    function isValue(value, def, is_return) {
        if ( $.type(value) == 'null'
            || $.type(value) == 'undefined'
            || $.trim(value) == '(en blanco)'
            || $.trim(value) == ''
            || ($.type(value) == 'number' && !$.isNumeric(value))
            || ($.type(value) == 'array' && value.length == 0)
            || ($.type(value) == 'object' && $.isEmptyObject(value)) ) {
            return ($.type(def) != 'undefined') ? def : false;
        } else {
            return ($.type(is_return) == 'boolean' && is_return === true ? value : true);
        }
    }
</script>