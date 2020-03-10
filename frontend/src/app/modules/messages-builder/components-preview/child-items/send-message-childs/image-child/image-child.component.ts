import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';

@Component({
  selector: 'app-image-child',
  templateUrl: './image-child.component.html',
  styleUrls: ['./image-child.component.scss']
})
export class ImageChildComponent implements OnInit {

  @Input() config: Child;
  @Input() opened: any;

  ngOnInit() {
  }

}
