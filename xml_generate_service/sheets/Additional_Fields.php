<Worksheet ss:Name="Additional Fields" ss:Protected="1">
<Table ss:ExpandedColumnCount="15" ss:ExpandedRowCount="107" x:FullColumns="1" x:FullRows="1" ss:StyleID="s62" ss:DefaultRowHeight="15">
<Column ss:StyleID="s99" ss:Width="120"/>
<Column ss:StyleID="s99" ss:Width="172.2"/>
<Column ss:StyleID="s99" ss:Width="120"/>
<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Source Data for Migration Object: Data Migration it.mds Service (Create and Change)</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Version S4CORE 103 - 09.09.2025 © Copyright SAP SE. All rights reserved.</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="12">
<Cell ss:MergeAcross="14" ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="13.950000000000001" ss:Hidden="1">
<Cell ss:StyleID="s62">
<Data ss:Type="String">ZMDSREQUEST_ADD_FLD</Data>
</Cell>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">M_COUNTER</Data>
</Cell>
<Cell>
<Data ss:Type="String">DESCRIPTION</Data>
</Cell>
<Cell>
<Data ss:Type="String">VALUE</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">ETE;30;0;C;30;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;30;0;C;30;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;150;0;C;150;0</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:MergeAcross="3" ss:StyleID="m525196716">
<Data ss:Type="String"/>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:StyleID="s108">
<Data ss:Type="String">Mig. Counter Type: Text Length: 30</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Add. Field Description Type: Text Length: 30</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Value Type: Text Length: 150</Data>
</Cell>
</Row>


<!-- DATA -->
<?php foreach ($details as $row): ?>

<?php
$mig = xe($row['mig_counter'] ?? '');

$fields = [
    [
        'desc' => 'ZMDS_ADDFLD_DMGR_APPROVAL',
        'value' => 'A'
    ],
    [
        'desc' => 'ZMDS_ADDFLD_LBPO_APPROVAL',
        'value' => 'A'
    ],
    [
        'desc' => 'ZMDS_ADDFLD_OK_BY_LMDM',
        'value' => 'X'
    ]
];
?>

<?php foreach ($fields as $f): ?>

<Row ss:AutoFitHeight="0">

    <!-- M_COUNTER -->
    <Cell>
        <Data ss:Type="String">
            <?= $mig ?>
        </Data>
    </Cell>

    <!-- DESCRIPTION -->
    <Cell>
        <Data ss:Type="String">
            <?= $f['desc'] ?>
        </Data>
    </Cell>

    <!-- VALUE -->
    <Cell>
        <Data ss:Type="String">
            <?= $f['value'] ?>
        </Data>
    </Cell>

</Row>

<?php endforeach; ?>
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
<Selected/>
<FreezePanes/>
<FrozenNoSplit/>
<SplitHorizontal>8</SplitHorizontal>
<TopRowBottomPane>8</TopRowBottomPane>
<SplitVertical>4</SplitVertical>
<LeftColumnRightPane>4</LeftColumnRightPane>
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
<ActiveRow>108</ActiveRow>
<ActiveCol>2</ActiveCol>
</Pane>
</Panes>
<ProtectObjects>False</ProtectObjects>
<ProtectScenarios>False</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>