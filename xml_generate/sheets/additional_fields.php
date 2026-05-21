<Worksheet ss:Name="Additional Fields">
<Table ss:ExpandedColumnCount="15"
    ss:ExpandedRowCount="<?= (count($details) * 3) + 8 ?>"
    x:FullColumns="1" x:FullRows="1" ss:StyleID="s62" ss:DefaultColumnWidth="52.8" ss:DefaultRowHeight="15">
<Column ss:StyleID="s108" ss:Width="120" ss:Span="2"/>

<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="2"><Data ss:Type="String">Source Data for Migration Object: Data migration it.mds Material (Create)</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="2"><Data ss:Type="String">Version SAP S/4HANA 2023 - Custom Scope - 16.12.2025 © Copyright SAP SE. All rights reserved.</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="12"><Cell ss:MergeAcross="2" ss:StyleID="s62"/></Row>

<Row ss:AutoFitHeight="0" ss:Height="13.95" ss:Hidden="1">
<Cell ss:StyleID="s62"><Data ss:Type="String">ZMDSREQUEST_ADD_FLD</Data></Cell>
<Cell ss:StyleID="s62"/><Cell ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell><Data ss:Type="String">M_COUNTER</Data></Cell>
<Cell><Data ss:Type="String">DESCRIPTION</Data></Cell>
<Cell><Data ss:Type="String">VALUE</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell><Data ss:Type="String">ETE;30;0;C;30;0</Data></Cell>
<Cell><Data ss:Type="String">ETE;30;0;C;30;0</Data></Cell>
<Cell><Data ss:Type="String">ETE;150;0;C;150;0</Data></Cell>
</Row>

<Row ss:AutoFitHeight="0">
<Cell ss:MergeAcross="2" ss:StyleID="s100"><Data ss:Type="String">Additional Fields</Data></Cell>
</Row>

<Row ss:AutoFitHeight="0" ss:Height="45">
<Cell ss:StyleID="s107"><Data ss:Type="String">Mig. Counter*&#10;&#10;Type: Text&#10;Length: 30</Data></Cell>
<Cell ss:StyleID="s107"><Data ss:Type="String">Add. Field Description*&#10;&#10;Type: Text&#10;Length: 30</Data></Cell>
<Cell ss:StyleID="s107"><Data ss:Type="String">Value*&#10;&#10;Type: Text&#10;Length: 150</Data></Cell>
</Row>

<?php 
// Pastikan variabel $details sudah berisi hasil mysqli_fetch_all dari DB
foreach ($details as $row): 
    // Ambil mig_counter langsung dari kolom database yang tadi kita isi
    $migCounter = xe($row['mig_counter'] ?? '');

    // List field tambahan untuk SAP
    $fields = [
        ['d' => 'ADD_FLD_NO_GEN_SCHEME',     'v' => 'U'],
        ['d' => 'ZMDS_ADDFLD_DMGR_APPROVAL', 'v' => 'A'],
        ['d' => 'ZMDS_ADDFLD_LBPO_APPROVAL', 'v' => 'A'],
    ];

    foreach ($fields as $f):
?>
<Row ss:AutoFitHeight="0">
    <Cell>
        <Data ss:Type="String"><?= $migCounter ?></Data>
    </Cell>

    <Cell>
        <Data ss:Type="String"><?= $f['d'] ?></Data>
    </Cell>

    <Cell>
        <Data ss:Type="String"><?= $f['v'] ?></Data>
    </Cell>
</Row>
<?php 
    endforeach; 
endforeach; 
?>

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
<RangeSelection>R9C1:R50C3</RangeSelection>
</Pane>
</Panes>
<ProtectObjects>False</ProtectObjects>
<ProtectScenarios>False</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>