import { Component, Input, OnInit } from '@angular/core';
import { Child } from '../../../../builder/builder-interface';

@Component({
  selector: 'app-delay-child',
  templateUrl: './delay-child.component.html',
  styleUrls: ['./delay-child.component.scss']
})
export class DelayChildComponent implements OnInit {

  @Input() config: Child;
  @Input() opened: any;

  ngOnInit() {
  }

}
