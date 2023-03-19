@props(['esri_address','name'=>''])

<?php // https://developers.arcgis.com/documentation/mapping-apis-and-services/search/ 
?>

@if(isset($esri_address))
<style>
    .esri-popup__main-container {
        width: auto !important;
    }

    .hidden {
        display: none;
    }
</style>
<div id="esri-map-holder" style="height:224px" class="hidden"></div>
<script type="module">
    require([
        "esri/config",
        "esri/Map",
        "esri/views/MapView",
        "esri/Graphic",
        "esri/rest/locator"
    ], (esriConfig, Map, MapView, Graphic, locator) => {

        esriConfig.apiKey = "YOUR_API_KEY"; //Your API key here

        const map = new Map({
            basemap: "arcgis-dark-gray" //Basemap layer service
        });

        const view = new MapView({
            container: "esri-map-holder",
            map: map,
            constraints: {
                snapToZoom: false
            }
        });

        view.popup.actions = [];
        view.popup.viewModel.includeDefaultActions = false;
        view.ui.move(["zoom"], "bottom-left");

        view.when(() => {

            const geocodingServiceUrl = "https://geocode-api.arcgis.com/arcgis/rest/services/World/GeocodeServer";
            const params = {
                address: {
                    address: "{{$esri_address}}"
                }
            };

            locator.addressToLocations(geocodingServiceUrl, params).then((results) => {
                showResult(results);
            });


            function showResult(results, _name = "<?php echo $name ?>") {
                if (results.length) {
                    const result = results[0];
                    view.goTo({
                        target: result.location,
                        zoom: 13
                    });
                    view.graphics.add(new Graphic({
                        symbol: {
                            type: "picture-marker",
                            url: "https://staging.mataction.com/storage/CDN_2/location-white.svg",
                            width: "20",
                            height: "20",
                        },
                        geometry: result.location,
                        attributes: {
                            address: result.address,
                        },
                        popupTemplate: {
                            title: _name
                        }
                    }));
                    const g = view.graphics.getItemAt(0);
                    view.popup.set("dockEnabled", true);
                    view.popup.set("dockOptions", {
                        breakpoint: false,
                        buttonEnabled: false,
                        position: "top-right"
                    });

                    if (_name !== "") {
                        view.popup.open({
                            features: [g],
                            location: g.geometry
                        });
                    }
                }
                document.getElementById("esri-map-holder").classList.remove("hidden");
            }
        });
    });
</script>
<link rel="stylesheet" href="https://js.arcgis.com/4.25/esri/themes/dark/main.css">
<script src="https://js.arcgis.com/4.25"></script>
@endif
