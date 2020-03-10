import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';

@Component({
  selector: 'app-video-child',
  templateUrl: './video-child.component.html',
  styleUrls: ['./video-child.component.scss']
})
export class VideoChildComponent implements OnInit {

  @Input() config: Child;
  @Input() opened: any;

  constructor() {

  }

  ngOnInit() {
  }

}
