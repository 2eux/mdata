<Worksheet ss:Name="Request Short Text">
<Table
    ss:ExpandedColumnCount="15"
    ss:ExpandedRowCount="<?= count($material_rows) + 8 ?>"
    x:FullColumns="1"
    x:FullRows="1"
    ss:StyleID="s62"
    ss:DefaultRowHeight="15">
<Column ss:StyleID="s108" ss:Width="120" ss:Span="2"/>

<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Source Data for Migration Object: Data migration it.mds Material (Create)</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Version SAP S/4HANA 2023 - Custom Scope - 16.12.2025 © Copyright SAP SE. All rights reserved.</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="12">
<Cell ss:MergeAcross="14" ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="13.950000000000001" ss:Hidden="1">
<Cell ss:StyleID="s62">
<Data ss:Type="String">ZMDSREQUESTMAKT</Data>
</Cell>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">M_COUNTER</Data>
</Cell>
<Cell>
<Data ss:Type="String">SPRAS</Data>
</Cell>
<Cell>
<Data ss:Type="String">MAKTX</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">ETE;30;0;C;30;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;2;0;C;2;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;40;0;C;40;0</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:MergeAcross="2" ss:StyleID="m1935128291132">
<Data ss:Type="String">Request MAKT</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:StyleID="s107">
<Data ss:Type="String">Mig. Counter* Type: Text Length: 30</Data>
</Cell>
<Cell ss:StyleID="s107">
<Data ss:Type="String">Language Key* Type: Text Length: 2</Data>
</Cell>
<Cell ss:StyleID="s107">
<Data ss:Type="String">Material description* Type: Text Length: 40</Data>
</Cell>
</Row>


<?php foreach ($material_rows as $row): ?>
<Row ss:AutoFitHeight="0">
    <Cell><Data ss:Type="String"><?= xe($row['mig_counter'] ?? '') ?></Data></Cell>
    
    <Cell><Data ss:Type="String">EN</Data></Cell>
    
    <Cell><Data ss:Type="String"><?= xe($row['description'] ?? '') ?></Data></Cell>
</Row>
<?php endforeach; ?>
</Table>
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<PageSetup>
<Header x:Margin="0.3"/>
<Footer x:Margin="0.3"/>
<PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
</PageSetup>
<Unsynced/>
<Print>
<ValidPrinterInfo/>
<PaperSizeIndex>9</PaperSizeIndex>
<HorizontalResolution>600</HorizontalResolution>
<VerticalResolution>600</VerticalResolution>
</Print>
<TabColorIndex>56</TabColorIndex>
<FreezePanes/>
<FrozenNoSplit/>
<SplitHorizontal>8</SplitHorizontal>
<TopRowBottomPane>8</TopRowBottomPane>
<SplitVertical>3</SplitVertical>
<LeftColumnRightPane>3</LeftColumnRightPane>
<ActivePane>0</ActivePane>
<Panes>
<Pane>
<Number>3</Number>
</Pane>
<Pane>
<Number>1</Number>
</Pane>
<Pane>
<Number>2</Number>
</Pane>
<Pane>
<Number>0</Number>
<ActiveCol>0</ActiveCol>
</Pane>
</Panes>
<ProtectObjects>False</ProtectObjects>
<ProtectScenarios>False</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>