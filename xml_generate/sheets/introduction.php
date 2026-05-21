<Worksheet ss:Name="Introduction" ss:Protected="1">
<Table ss:ExpandedColumnCount="9" ss:ExpandedRowCount="83" x:FullColumns="1" x:FullRows="1" ss:StyleID="s62" ss:DefaultRowHeight="15">
<Column ss:StyleID="s62" ss:AutoFitWidth="0" ss:Width="19.8"/>
<Column ss:StyleID="s62" ss:AutoFitWidth="0" ss:Width="14.4"/>
<Column ss:StyleID="s62" ss:AutoFitWidth="0" ss:Width="19.8"/>
<Column ss:StyleID="s62" ss:AutoFitWidth="0" ss:Width="671.4"/>
<Column ss:StyleID="s62" ss:AutoFitWidth="0" ss:Width="14.4"/>
<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="8">
<Data ss:Type="String">Introduction to Data Migration Templates</Data>
<NamedCell ss:Name="Instructions"/>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="8">
<Data ss:Type="String">Version SAP S/4HANA 2023 - Custom Scope - 16.12.2025 © Copyright SAP SE. All rights reserved.</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:MergeAcross="4"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Overview </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="60">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">A migration template (Microsoft Excel XML Spreadsheet 2003 file) consists of different sheets which are visible at the bottom of the migration template. You use the different sheets to specify the data that belongs to different data structures. For example the migration template for the migration object 'Product', contains a sheet for basic data, a sheet for plant data, and so on. Some sheets are mandatory, and some are optional. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s73">
<Data ss:Type="String">A migration template is based on the active view of the relevant migration object. You can find information about the active view in the Microsoft Excel XML file. In the file, navigate to File -> Info. You can find the active view name under Properties -> Tags. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Prerequisites </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">In the Microsoft Excel XML Spreadsheet 2003 file, navigate to 'File' -> 'Options' -> 'Advanced'. Under the option 'When calculating this workbook:', ensure that the option 'Set precision as displayed' is selected. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Information about File Sizes </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">SAP S/4HANA Cloud </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">If you are migrating data to SAP S/4HANA Cloud, the default size limit for each uploaded XML file is 100MB. If required, you can zip several files together. Note that the combined size of all the XML files you want to add to the zip file must not exceed 160MB. The limit for zip file is still 100MB. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">SAP S/4HANA On-Premise </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">If you are migrating data to SAP S/4HANA, the default size limit for each uploaded XML file is 100MB. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">You can increase the size limit for each uploaded XML file to 160MB by changing the system parameter (icm/HTTP/max_request_size_KB). If required, you can zip several files together. Note that the combined size of all the XML files you want to add to the zip file must not exceed 160MB. The limit for the zip file is still 160 MB with the adjusted system parameter. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">The Field List Sheet </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">The 'Field List' sheet is one of the first sheets in the migration template. You use this sheet to get an overview of the expected data in one central location. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">It contains information about the mandatory and optional sheets, as well as detailed information for each sheet (for example the expected data type and length for the fields in each sheet). </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">On the 'Field List' sheet, you can view the following information for each field in the migration template: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="25.5">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">The name of the sheet, and whether it is mandatory or optional. Only mandatory sheets have the suffix 'Mandatory', for example 'Basic Data (Mandatory)'. All other sheets are optional. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">You can quickly get an overview of the mandatory and optional sheets by looking at the color of the sheet names at the bottom of the migration template. The name of the mandatory sheets have the color orange, while the optional sheets have the color blue. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">The group name for the fields in a sheet. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">The individual fields in each sheet, as well as whether fields are mandatory for a sheet. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Information about the expected format of the individual fields, for example the data type and length. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="60">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">In the field list sheet, certain technical information about the fields is hidden by default. The columns 'SAP Structure' and 'SAP Field' (columns 8 and 9) are hidden by default. The column 'SAP Structure' is the technical name of the structure that the field belongs to. The column 'SAP Field' is the technical name of the field. To unhide these columns, select the columns adjacent to either side of the columns that you want to unhide. Right-click your selection, and choose 'Unhide'. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">If you want to extract data from SAP ERP, the technical name of structure and the technical name of the field often corresponds to the SAP ERP table name and field name. Also, the SAP Release Note (2568909) for SAP S/4HANA Cloud data migration content uses the technical names of the structures and fields. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Working with Sheets </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">For each migration template, you need to specify data for the mandatory sheets, and for the optional sheets that are relevant for your project: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Mandatory sheets (orange) </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Mandatory sheets represent the minimum set of data you must provide for data migration. Fill in all mandatory fields. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Optional sheets (blue) </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Use optional sheets depending on your migration scope and available legacy data. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Viewing Additional Information for Each Column </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">In row 8, you can view the field names in SAP S/4HANA, as well as additional information such as the expected format (for example the data type and length). Note that you must expand the row to view this additional information. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Some fields are mandatory, and some are optional. The wildcard character (‘*’) beside the name of a field indicates that the field is mandatory. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Although an optional sheet may contain mandatory columns, if the sheet is not relevant for your project, there is no need to fill the mandatory columns in the sheet with data. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Rows 4, 5, and 6 are hidden by default. Row 4 is the technical name of the structure (corresponds to the sheet name). Row 5 is the technical name of the field (corresponds to row 8 - the field description). Row 6 contains technical information such as the data type and length. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">To unhide these rows, select the rows adjacent to either side of the rows that you want to unhide. Right-click your selection, and choose 'Unhide'. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Working with Different Data Types </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">You can view the data type for a field in row 8 (see 'Viewing Additional Information for Each Column' above). Depending on the field, one of the following data types may be required: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Text </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Letters, numbers, and special characters are permitted. In the SAP S/4HANA migration cockpit, you can map the values of certain fields with the data type text (usually those fields with Length: 80) to their correct SAP S/4HANA target values. You can do this value mapping in the SAP S/4HANA migration cockpit when you start the transfer (in the step 'Convert Values'). </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Number </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="90">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Enter numbers with decimals in the relevant country-specific format, for example 12.34 (United States) or 12,34 (Germany). For fields with decimals, the declared length includes decimals (if required), for example if the information for the column states: Length: 8, Decimals: 3, then a number such as 12345.678 is permitted. Note that decimal places are not mandatory. In this example, you can specify a whole number up to length 8 without decimal places, for example ‘1’. This number would be set to ‘1.000’ internally. For negative numbers, ensure that a minus sign ('-') directly precedes the number, for example '-100'. Note that currencies with more than 3 decimal places are not supported. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note that the maximum field length supported by Microsoft Excel is 15 digits (including decimals). If you have longer numbers, use the option for transferring data to S/4HANA using staging tables. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Date </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Enter the date in your country-specific format, for example 12/31/1998 (United States) or 31.12.1998 (Germany). Note that Microsoft Excel automatically recognizes different date formats and transforms them automatically to the correct XML format. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Time </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Enter the time in the format HH:MM:SS, for example 02:52:40 </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Copying Data to a Sheet </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">When copying data to a sheet from Microsoft Excel, always right-click the relevant cell and choose the paste option 'Values (V)'. Avoid pasting data that includes formatting and formulas into the migration template, as this will corrupt the structure of the XML migration template. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Using the Find and Replace Function </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Do not use the Microsoft Excel function 'Find and Replace'. If you change data by using this function, you may also unintentionally change the field names and corrupt the structure of the XML migration template. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Saving the Migration Template </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Ensure that you only save the migration template as a Microsoft Excel XML Spreadsheet 2003 file. Other file types are not supported by the SAP S/4HANA migration cockpit. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s70">
<Data ss:Type="String">Important Information </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Do not make any changes to the structure of the migration template, specifically: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Do not delete, rename or change the order of any sheet in the migration template. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Do not change the formatting of any cells. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Do not use formulas. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68">
<Data ss:Type="String">•</Data>
</Cell>
<Cell ss:StyleID="s68">
<Data ss:Type="String">Do not hide, remove, or change the order of any of the columns in the migration template. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Note: </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="30">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String">Any changes to the sheets will result in a corrupted XML structure. Such modified migration templates are not supported by the SAP S/4HANA migration cockpit. </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:MergeAcross="1" ss:StyleID="s68">
<Data ss:Type="String"> </Data>
</Cell>
<Cell ss:StyleID="s68"/>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:Index="2" ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
<Cell ss:StyleID="s68"/>
</Row>
</Table>
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<PageSetup>
<Header x:Margin="0.3"/>
<Footer x:Margin="0.3"/>
<PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
</PageSetup>
<Unsynced/>
<FitToPage/>
<Print>
<FitHeight>99</FitHeight>
<ValidPrinterInfo/>
<PaperSizeIndex>9</PaperSizeIndex>
<HorizontalResolution>600</HorizontalResolution>
<VerticalResolution>600</VerticalResolution>
</Print>
<TabColorIndex>9</TabColorIndex>
<FreezePanes/>
<FrozenNoSplit/>
<SplitHorizontal>3</SplitHorizontal>
<TopRowBottomPane>3</TopRowBottomPane>
<ActivePane>2</ActivePane>
<Panes>
<Pane>
<Number>3</Number>
</Pane>
<Pane>
<Number>2</Number>
<ActiveCol>2</ActiveCol>
</Pane>
</Panes>
<ProtectObjects>True</ProtectObjects>
<ProtectScenarios>True</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>