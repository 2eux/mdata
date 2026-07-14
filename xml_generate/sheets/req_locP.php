<Worksheet ss:Name="Request Local Profile">
<Table x:FullColumns="1" x:FullRows="1" ss:StyleID="s62" ss:DefaultColumnWidth="52.8" ss:DefaultRowHeight="15"
    ss:ExpandedColumnCount="2" 
    ss:ExpandedRowCount="<?= (count($details) * 2) + 8 ?>"> <Column ss:StyleID="s99" ss:Width="120" ss:Span="1"/>

<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="1"><Data ss:Type="String">Source Data for Migration Object: Data migration it.mds Material (Create)</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="1"><Data ss:Type="String">Version SAP S/4HANA 2023 - Custom Scope - 16.12.2025 © Copyright SAP SE. All rights reserved.</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="12"><Cell ss:MergeAcross="1" ss:StyleID="s62"/></Row>

<Row ss:AutoFitHeight="0" ss:Height="13.95" ss:Hidden="1">
<Cell ss:StyleID="s62"><Data ss:Type="String">ZMDSLOCAL_PROFILE</Data></Cell>
<Cell ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell><Data ss:Type="String">M_COUNTER</Data></Cell>
<Cell><Data ss:Type="String">PROFILE</Data></Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell><Data ss:Type="String">ETE;30;0;C;30;0</Data></Cell>
<Cell><Data ss:Type="String">ETE;30;0;C;30;0</Data></Cell>
</Row>

<Row ss:AutoFitHeight="0">
<Cell ss:MergeAcross="1" ss:StyleID="s100"><Data ss:Type="String">Local Profile</Data></Cell>
</Row>

<Row ss:AutoFitHeight="0">
<Cell ss:StyleID="s107"><Data ss:Type="String">Mig. Counter Type: Text Length: 30</Data></Cell>
<Cell ss:StyleID="s107"><Data ss:Type="String">Local Profile name Type: Text Length: 30</Data></Cell>
</Row>

<?php foreach ($details as $row): 
    // Mengambil data dari database
    $id_counter = xe($row['mig_counter'] ?? '');
?>
    <Row ss:AutoFitHeight="0">
        <Cell><Data ss:Type="String"><?= $id_counter ?></Data></Cell>
        <Cell><Data ss:Type="String">NO_PROC</Data></Cell>
    </Row>

    <Row ss:AutoFitHeight="0">
        <Cell><Data ss:Type="String"><?= $id_counter ?></Data></Cell>
        <Cell><Data ss:Type="String">NO_PLANNING</Data></Cell>
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
<TopRowBottomPane>26</TopRowBottomPane>
<SplitVertical>2</SplitVertical>
<LeftColumnRightPane>2</LeftColumnRightPane>
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
<ActiveRow>8</ActiveRow>
</Pane>
<Pane>
<Number>0</Number>
<ActiveRow>8</ActiveRow>
<ActiveCol>0</ActiveCol>
<RangeSelection>R9C1:R36C2</RangeSelection>
</Pane>
</Panes>
<ProtectObjects>False</ProtectObjects>
<ProtectScenarios>False</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>