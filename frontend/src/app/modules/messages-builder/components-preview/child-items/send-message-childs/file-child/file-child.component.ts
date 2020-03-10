import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';

@Component({
  selector: 'app-file-child',
  templateUrl: './file-child.component.html',
  styleUrls: ['./file-child.component.scss']
})
export class FileChildComponent implements OnInit {

  @Input() config: Child;
  @Input() opened: any;

  ngOnInit() {
  }

}
