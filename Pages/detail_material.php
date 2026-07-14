<!DOCTYPE html>
<html>
<head>
    <title>Material Detail</title>    
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="css/request.css">
</head>

<body>

<div class="container">

<h2>Material Detail</h2>

<div class="card-req" id="detailBox"></div>

<br>
<button onclick="window.history.back()">← Back</button>

</div>

<script>
let data = JSON.parse(localStorage.getItem("material_detail"));

let labelMap = {
    image_url: "Image Preview",
    numbering_scheme: "Numbering Scheme",
    material_number: "Material Number",
    description: "Description",
    description_alt: "Description Alt",
    uom: "UOM",
    ext_material_group: "Ext Material Group",
    material_group: "Material Group",
    material_type: "Material Type",
    vhs: "VHS",
    location: "Location",
    val_class: "Val Class",
    val_category: "Val Category",
    purchasing_group: "Purchasing Group",
    mrp_controller: "MRP Controller",
    price_control: "Price Control",
    remarks: "Remarks"
};

let html = '';

for(let key in data){

    // IMAGE
    if(key === "image_url" && data[key]){
        html += `
        <div style="margin-bottom:15px;">
            <label style="font-size:12px;color:#666;">Image Preview</label><br>

           <img id="previewImage"
            src="${data[key] || ''}" 
            style="max-width:200px;border-radius:8px;border:1px solid #ccc;display:${data[key] ? 'block' : 'none'};margin-bottom:8px;"
            onerror="this.style.display='none'">

            <input   
                data-key="${key}"
                value="${data[key]}"
                oninput="updateField(this)"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
        </div>`;
    } 
    
    else {
        html += `
        <div style="margin-bottom:12px;">
            <label style="font-size:12px;color:#666;">
                ${labelMap[key] || key}
            </label>

            <input 
                data-key="${key}"
                value="${data[key]}"
                oninput="updateField(this)"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
        </div>`;
    }
}

document.getElementById("detailBox").innerHTML = html;

function updateField(input){
    let key = input.dataset.key;

    data[key] = input.value;

    localStorage.setItem(
        "material_detail",
        JSON.stringify(data)
    );

    // update preview image realtime
    if(key === "image_url"){
        let img = document.getElementById("previewImage");

        img.src = input.value;

        if(input.value){
            img.style.display = "block";
        }
    }
}
</script>

</body>
</html>